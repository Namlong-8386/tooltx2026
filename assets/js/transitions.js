/**
 * Page Transition Effects Handler
 * Xử lý hiệu ứng chuyển trang mượt mà
 */

class PageTransition {
    constructor(options = {}) {
        this.duration = options.duration || 600;
        this.effect = options.effect || 'fade'; // fade, slide, zoom
        this.enabled = options.enabled !== false;
        this.init();
    }

    init() {
        if (!this.enabled) return;
        
        // Tạo overlay element
        this.createOverlay();
        
        // Lắng nghe sự kiện click trên các link
        this.attachLinkListeners();
        
        // Xử lý back/forward button
        window.addEventListener('popstate', () => {
            // this.showTransition();
        });
    }

    createOverlay() {
        const overlay = document.createElement('div');
        overlay.className = 'page-transition-overlay no-transition';
        overlay.id = 'page-transition-overlay';
        document.body.appendChild(overlay);
        
        // Tạo spinner
        const spinner = document.createElement('div');
        spinner.className = 'transition-spinner no-transition';
        spinner.id = 'transition-spinner';
        spinner.innerHTML = '<div class="spinner"></div>';
        document.body.appendChild(spinner);
    }

    attachLinkListeners() {
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            
            // Bỏ qua các link không phải navigation
            if (!link || 
                link.target === '_blank' || 
                link.href.startsWith('#') ||
                link.href.startsWith('javascript:') ||
                link.classList.contains('no-transition') ||
                !link.href.startsWith(window.location.origin)) {
                return;
            }

            // Bỏ qua form submission
            if (link.closest('form')) return;

            e.preventDefault();
            this.navigateTo(link.href);
        });
    }

    navigateTo(url) {
        // Just navigate without showing transition
        window.location.href = url;
    }

    showTransition() {
        const overlay = document.getElementById('page-transition-overlay');
        const spinner = document.getElementById('transition-spinner');
        
        if (!overlay || !spinner) return;

        // Hiển thị overlay và spinner
        overlay.classList.add('active');
        spinner.classList.add('active');
        
        // Ẩn sau khi hoàn thành
        setTimeout(() => {
            overlay.classList.remove('active');
            spinner.classList.remove('active');
        }, this.duration);
    }

    // Hiệu ứng khi trang vừa load
    pageEnter() {
        // document.body.classList.add('page-enter');
        // setTimeout(() => {
        //     document.body.classList.remove('page-enter');
        // }, this.duration);
    }
}

// Khởi tạo khi DOM sẵn sàng
document.addEventListener('DOMContentLoaded', () => {
    // Khởi tạo page transition
    const transition = new PageTransition({
        duration: 600,
        effect: 'fade',
        enabled: true
    });

    // Hiệu ứng khi trang vừa load
    transition.pageEnter();

    // Tùy chọn: Thêm smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Xuất ra global scope nếu cần sử dụng từ nơi khác
window.PageTransition = PageTransition;
