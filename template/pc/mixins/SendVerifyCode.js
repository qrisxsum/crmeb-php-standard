export default {
  data() {
    return {
      disabled: false,
      text: "获取验证码",
      runTime: null,
      codeEndAt: 0
    };
  },
  mounted() {
    const endAt = Number(window.localStorage.getItem("pc_verify_code_end_at") || 0);
    if (endAt > Date.now()) {
      this.codeEndAt = endAt;
      this.startCountdown();
    } else if (endAt) {
      window.localStorage.removeItem("pc_verify_code_end_at");
    }
  },
  beforeDestroy() {
    if (this.runTime) clearInterval(this.runTime);
  },
  methods: {
    sendCode() {
      if (this.disabled) return;
      const seconds = 60;
      this.codeEndAt = Date.now() + seconds * 1000;
      window.localStorage.setItem("pc_verify_code_end_at", String(this.codeEndAt));
      this.startCountdown();
    },
    startCountdown() {
      if (this.runTime) clearInterval(this.runTime);
      this.disabled = true;
      const tick = () => {
        const remain = Math.ceil((this.codeEndAt - Date.now()) / 1000);
        if (remain <= 0) {
          if (this.runTime) clearInterval(this.runTime);
          this.runTime = null;
          this.codeEndAt = 0;
          window.localStorage.removeItem("pc_verify_code_end_at");
          this.disabled = false;
          this.text = "重新获取";
          return;
        }
        this.text = "剩余 " + remain + "s";
      };
      tick();
      this.runTime = setInterval(tick, 1000);
    }
  }
};
