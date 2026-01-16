<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\services\mail\storage;

use crmeb\services\mail\BaseMail;
use think\facade\Log as ThinkLog;

class Smtp extends BaseMail
{
    protected $host = '';
    protected $port = 25;
    protected $username = '';
    protected $password = '';
    protected $encryption = 'none'; // none|tls|ssl
    protected $fromEmail = '';
    protected $fromName = '';
    protected $helo = 'localhost';
    protected $timeout = 10;

    protected function initialize(array $config)
    {
        $this->host = (string)($config['host'] ?? '');
        $this->port = (int)($config['port'] ?? 25);
        $this->username = (string)($config['username'] ?? '');
        $this->password = (string)($config['password'] ?? '');
        $this->encryption = (string)($config['encryption'] ?? 'none');
        $this->fromEmail = (string)($config['from_email'] ?? '');
        $this->fromName = (string)($config['from_name'] ?? '');
        $this->helo = (string)($config['helo'] ?? 'localhost');
        $this->timeout = (int)($config['timeout'] ?? 10);
    }

    public function sendVerifyCode(string $email, string $code, int $expireMinutes, array $context = []): bool
    {
        if (!$this->host) return $this->fail('SMTP host 未配置');
        if (!$this->fromEmail) return $this->fail('SMTP from_email 未配置');

        $subject = '验证码';
        $type = (string)($context['type'] ?? '');
        if ($type === 'login') $subject = '登录验证码';
        if ($type === 'register') $subject = '注册验证码';

        $body = '您的验证码为：' . $code . "\n";
        $body .= '有效期：' . $expireMinutes . "分钟\n";
        $body .= "请勿泄露给他人。\n";

        $headers = [
            'From' => $this->formatAddress($this->fromEmail, $this->fromName),
            'To' => $this->formatAddress($email, ''),
            'Subject' => $this->encodeHeader($subject),
            'Date' => gmdate('D, d M Y H:i:s') . ' +0000',
            'Message-ID' => '<' . bin2hex(random_bytes(16)) . '@' . $this->helo . '>',
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Transfer-Encoding' => '8bit',
        ];

        $raw = '';
        foreach ($headers as $k => $v) {
            $raw .= $k . ': ' . $v . "\r\n";
        }
        $raw .= "\r\n" . $this->normalizeBody($body);

        try {
            return $this->smtpSend($email, $raw);
        } catch (\Throwable $e) {
            ThinkLog::error('[mail.smtp] sendVerifyCode failed', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            return $this->fail('邮件发送失败');
        }
    }

    protected function smtpSend(string $rcptTo, string $rawMessage): bool
    {
        $remoteHost = $this->host;
        $remotePort = $this->port;
        $transport = 'tcp';

        $encryption = strtolower(trim((string)$this->encryption));
        if ($encryption === 'ssl') $transport = 'ssl';

        $socket = null;
        $errno = 0;
        $errstr = '';
        if ($transport === 'ssl') {
            $socket = @fsockopen('ssl://' . $remoteHost, $remotePort, $errno, $errstr, $this->timeout);
        } else {
            $socket = @fsockopen($remoteHost, $remotePort, $errno, $errstr, $this->timeout);
        }
        if (!$socket) {
            $detail = trim((string)$errstr);
            if ($detail === '') $detail = 'errno=' . (string)$errno;
            return $this->fail('SMTP 连接失败: host=' . $remoteHost . ' port=' . $remotePort . ' ' . $detail);
        }

        stream_set_timeout($socket, $this->timeout);

        $this->expect($socket, [220]);
        $ehloLines = $this->command($socket, 'EHLO ' . $this->helo, [250]);

        if ($encryption === 'tls') {
            $supportsStartTls = false;
            foreach ($ehloLines as $line) {
                if (stripos($line, 'STARTTLS') !== false) {
                    $supportsStartTls = true;
                    break;
                }
            }
            if ($supportsStartTls) {
                $this->command($socket, 'STARTTLS', [220]);
                $cryptoOk = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                if (!$cryptoOk) return $this->fail('SMTP STARTTLS 握手失败');
                $ehloLines = $this->command($socket, 'EHLO ' . $this->helo, [250]);
            }
        }

        if ($this->username !== '') {
            $this->authLogin($socket);
        }

        $this->command($socket, 'MAIL FROM:<' . $this->fromEmail . '>', [250]);
        $this->command($socket, 'RCPT TO:<' . $rcptTo . '>', [250, 251]);
        $this->command($socket, 'DATA', [354]);

        $data = $this->dotStuff($rawMessage) . "\r\n.\r\n";
        $this->write($socket, $data);
        $this->expect($socket, [250]);

        $this->command($socket, 'QUIT', [221, 250]);
        fclose($socket);
        return true;
    }

    protected function fail(string $message): bool
    {
        ThinkLog::error('[mail.smtp] ' . $message);
        return $this->setError($message);
    }

    protected function authLogin($socket): void
    {
        $this->command($socket, 'AUTH LOGIN', [334]);
        $this->command($socket, base64_encode($this->username), [334]);
        $this->command($socket, base64_encode($this->password), [235]);
    }

    protected function formatAddress(string $email, string $name): string
    {
        $email = trim($email);
        $name = trim($name);
        if ($name === '') return '<' . $email . '>';
        return $this->encodeHeader($name) . ' <' . $email . '>';
    }

    protected function encodeHeader(string $value): string
    {
        if ($value === '') return $value;
        if (preg_match('/^[\x20-\x7E]+$/', $value)) return $value;
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    protected function normalizeBody(string $body): string
    {
        $body = str_replace(["\r\n", "\r"], "\n", $body);
        $body = str_replace("\n", "\r\n", $body);
        return $body;
    }

    protected function dotStuff(string $data): string
    {
        $data = $this->normalizeBody($data);
        return preg_replace("/\r\n\./", "\r\n..", $data) ?? $data;
    }

    protected function write($socket, string $data): void
    {
        $len = strlen($data);
        $written = 0;
        while ($written < $len) {
            $n = fwrite($socket, substr($data, $written));
            if ($n === false || $n === 0) {
                throw new \RuntimeException('SMTP 写入失败');
            }
            $written += $n;
        }
    }

    protected function command($socket, string $command, array $expectCodes): array
    {
        $this->write($socket, $command . "\r\n");
        return $this->expect($socket, $expectCodes);
    }

    protected function expect($socket, array $expectCodes): array
    {
        $lines = [];
        $code = null;
        while (!feof($socket)) {
            $line = fgets($socket, 8192);
            if ($line === false) break;
            $line = rtrim($line, "\r\n");
            $lines[] = $line;
            if (preg_match('/^(\d{3})([ -])/', $line, $m)) {
                $code = (int)$m[1];
                $isLast = ($m[2] === ' ');
                if ($isLast) break;
            }
        }
        if ($code === null) {
            throw new \RuntimeException('SMTP 响应为空');
        }
        foreach ($expectCodes as $ok) {
            if ($code === (int)$ok) return $lines;
        }
        throw new \RuntimeException('SMTP 响应码异常: ' . $code . ' lines=' . json_encode($lines, JSON_UNESCAPED_UNICODE));
    }
}
