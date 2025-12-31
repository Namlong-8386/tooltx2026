/**
 * Website Security & Anti-Crack Module
 * Chặn DevTools, phím tắt kiểm tra code và bảo vệ nội dung
 */

(function() {
    'use strict';

    // 1. Chặn chuột phải
    document.addEventListener('contextmenu', e => e.preventDefault());

    // 2. Chặn các phím tắt phổ biến để mở DevTools
    document.addEventListener('keydown', e => {
        // F12
        if (e.keyCode === 123) {
            e.preventDefault();
            return false;
        }
        // Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+Shift+C
        if (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74 || e.keyCode === 67)) {
            e.preventDefault();
            return false;
        }
        // Ctrl+U (Xem nguồn trang)
        if (e.ctrlKey && e.keyCode === 85) {
            e.preventDefault();
            return false;
        }
        // Ctrl+S (Lưu trang)
        if (e.ctrlKey && e.keyCode === 83) {
            e.preventDefault();
            return false;
        }
    });

    // 3. Phát hiện DevTools đang mở bằng debugger
    // Disabled to prevent interference with app timers
    // setInterval(() => {
    //     const startTime = performance.now();
    //     debugger;
    //     const endTime = performance.now();
    //     if (endTime - startTime > 100) {
    //         window.location.reload();
    //     }
    // }, 1000);

    // 4. Chặn console log để tránh lộ thông tin
    if (typeof console !== "undefined") {
        const methods = ["log", "warn", "info", "error", "debug", "table"];
        methods.forEach(method => {
            console[method] = function() {};
        });
    }

    // 5. Tự động chuyển hướng nếu phát hiện iframe lạ (chống clickjacking)
    // Disabled for Replit development environment (iframe preview)
    // if (window.self !== window.top) {
    //     window.top.location = window.self.location;
    // }

    console.log("%cSTOP!", "color: red; font-family: sans-serif; font-size: 4.5em; font-weight: bolder; text-shadow: #000 1px 1px;");
    console.log("%cĐây là tính năng trình duyệt dành cho nhà phát triển. Nếu ai đó bảo bạn sao chép-dán nội dung vào đây để 'hack' website, đó là một trò lừa đảo.", "font-family: sans-serif; font-size: 1.5em; font-weight: bolder;");
})();
