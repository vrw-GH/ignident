/**
 * FloatingButton
 * A JavaScript class for creating customizable floating button.
 *
 * @version 1.0.0
 * @license MIT License
 * @author Dmytro Lobov
 */

'use strict';

class FloatingButton {

    constructor(options) {

        const defaultOptions = {
            showAfterTimer: false,
            hideAfterTimer: false,
            showAfterPosition: false,
            hideAfterPosition: false,
            uncheckedBtn: false,
            uncheckedSubBtn: false,
            hideBtns: false,

        };

        this.settings = {...defaultOptions, ...options};
        this.element = document.getElementById(this.settings.element);
    }

    activeLikeBtn() {
        const likeBtn = this.element.querySelector('[data-btn-type="like"]');

        if (!likeBtn) {
            return false;
        }

        if (!this.settings.pageId) {
            const parent = likeBtn.closest('li');
            if(parent) {
                parent.remove();
            } else {
                this.element.remove();
            }
            return false;
        }

        if (parseInt(localStorage.getItem('likePost')) === this.settings.pageId) {
            likeBtn.classList.add('_active');
        }

    }

    displayMenu() {

        if(this.settings.showAfterTimer || this.settings.hideAfterTimer || this.settings.hideAfterPosition ||  this.settings.showAfterPosition) {

            this.settings.showAfterTimer && this.showAfterTimer(this.settings.showAfterTimer);
            this.settings.hideAfterTimer && this.hideAfterTimer(this.settings.hideAfterTimer);
            this.settings.hideAfterPosition && this.hideAfterPosition(this.settings.hideAfterPosition);
            this.settings.showAfterPosition && this.showAfterPosition(this.settings.showAfterPosition);
        } else {
            this.showMenu();
        }

    }

    showMenu() {
        this.element.classList.remove('is-hidden');

    }

    hideMenu() {
        this.element.classList.add('is-hidden');

    }

    showAfterTimer() {
        setTimeout(() => this.showMenu(), this.settings.showAfterTimer * 1000);

    }

    hideAfterTimer() {

        setTimeout(() => this.element.classList.add('is-hidden'), this.settings.hideAfterTimer * 1000);
    }

    hideAfterPosition() {

        window.addEventListener('scroll', () => {
            const shouldHide = window.scrollY > this.settings.hideAfterPosition;
            if (!shouldHide) {
                this.showMenu();
            } else {
                this.hideMenu();
            }
        });
    }

    showAfterPosition() {

        window.addEventListener('scroll', () => {
            const shouldHide = window.scrollY <= this.settings.showAfterPosition;
            if (!shouldHide) {
                this.showMenu();
            } else {
                this.hideMenu();
            }
        });
    }

    actionServices() {
        this.element.addEventListener('click', (event) => {
            const target = event.target.closest('[data-btn-type]');
            if (!target) return;

            event.preventDefault();
            const action = target.getAttribute('data-btn-type');
            this.handleAction(action, target);
        });
    }

    handleAction(action, link) {
        const actionMap = {
            print: () => window.print(),
            back: () => window.history.back(),
            forward: () => window.history.forward(),
            scroll: () => {
                const anchor = link.getAttribute('href');
                const element = document.querySelector(anchor);
                element.scrollIntoView({behavior: 'smooth', block: 'start', inline: 'nearest'});
            },
            toTop: () => window.scrollTo({top: 0, behavior: 'smooth'}),
            tobottom: () => window.scrollTo({top: document.body.scrollHeight, behavior: 'smooth'}),
            like: () => this.setLikes(link),

        };

        const actionFunction = actionMap[action];
        if (actionFunction) {
            actionFunction();
        }
    }

    setLikes(link) {
        const counter = link.getAttribute('data-counter');
        const item = localStorage.getItem('likePost');
        if (this.settings.pageId && this.settings.pageId !== parseInt(item)) {
            const nexCounter = parseInt(counter) + 1;
            let data = {
                'action': 'wowp_likes',
                'post_id': this.settings.pageId, // Enter the id of the post
                '_ajax_nonce': wowp_flBtn.nonce // replace with how you have localized your nonce
            };

            let body = new URLSearchParams();
            for (let key in data) {
                body.append(key, data[key]);
            }

            fetch(wowp_flBtn.ajax_url, { // replace with how you have localized your ajaxurl
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: body.toString()
            })
                .then(data => {
                    localStorage.setItem('likePost', this.settings.pageId);
                    link.setAttribute('data-counter', nexCounter);
                    link.classList.add('_active');
                })
                .catch(error => console.error('Error:', error));


        }
    }

    shareServices() {
        const links = this.element.querySelectorAll('[data-share]');
        links.forEach(link => {
            link.addEventListener('click', () => {
                this.shareThis(link);
            });
        });
    }

    shareThis(link) {
        const service = link.getAttribute('data-share');
        const shareData = this.defaultShareServices();
        const shareService = shareData[service];

        const winWidth = 550;
        const winHeight = 450;
        const topPosition = (screen.height - winHeight) / 2;
        const leftPosition = (screen.width - winWidth) / 2;

        const winParams = `
    menubar=no,
    toolbar=no,
    location=no,
    scrollbars=no,
    status=no,
    resizable=yes,
    width=${winWidth},
    height=${winHeight},
    top=${topPosition},
    left=${leftPosition}
  `;

        window.open(shareService.url, null, winParams);
    }

    defaultShareServices() {
        const PAGE_URL = encodeURIComponent(document.location.href);
        const PAGE_TITLE = encodeURIComponent(document.title);

        return {
            'blogger': {
                url: `https://www.blogger.com/blog-this.g?u=${PAGE_URL}&n=${PAGE_TITLE}`,
            },
            'buffer': {
                url: `https://buffer.com/add?text=${PAGE_TITLE}&url=${PAGE_URL}`,
            },
            'diaspora': {
                url: `https://share.diasporafoundation.org/?title=${PAGE_TITLE}&url=${PAGE_URL}`,
            },
            'digg': {
                url: `http://digg.com/submit?url=${PAGE_URL}`,
            },
            'douban': {
                url: `http://www.douban.com/recommend/?url=${PAGE_URL}&title=${PAGE_TITLE}`,
            },

            'draugiem': {
                url: `https://www.draugiem.lv/say/ext/add.php?title=${PAGE_TITLE}&url=${PAGE_URL}`,
            },
            'email': {
                url: `mailto:?subject=${PAGE_TITLE}&body=${PAGE_URL}`,
            },
            'evernote': {
                url: `http://www.evernote.com/clip.action?url=${PAGE_URL}&title=${PAGE_TITLE}`,
            },
            'facebook': {
                url: `https://www.facebook.com/sharer.php?u=${PAGE_URL}`,
            },
            'flipboard': {
                url: `https://share.flipboard.com/bookmarklet/popout?v=2&title=${PAGE_TITLE}&url=${PAGE_URL}`,
            },
            'google-bookmarks': {
                url: `https://www.google.com/bookmarks/mark?op=edit&bkmk=${PAGE_URL}&title=${PAGE_TITLE}`,
            },
            'googleplus': {
                url: `https://plus.google.com/share?url=${PAGE_URL}`,
            },
            'hacker-news': {
                url: `https://news.ycombinator.com/submitlink?u=${PAGE_URL}&t=${PAGE_TITLE}`,
            },
            'instapaper': {
                url: `http://www.instapaper.com/edit?url=${PAGE_URL}&title=${PAGE_TITLE}`,
            },
            'line': {
                url: `https://lineit.line.me/share/ui?url=${PAGE_URL}`,
            },
            'linkedin': {
                url: `https://www.linkedin.com/shareArticle?mini=true&url=${PAGE_URL}&title=${PAGE_TITLE}`,
            },
            'livejournal': {
                url: `http://www.livejournal.com/update.bml?subject=${PAGE_TITLE}&event=${PAGE_URL}`,
            },
            'myspace': {
                url: `https://myspace.com/post?u=${PAGE_URL}&t=${PAGE_TITLE}`,
            },
            'odnoklassniki': {
                url: `https://connect.ok.ru/dk?st.cmd=WidgetSharePreview&st.shareUrl=${PAGE_URL}`,
            },
            'pinterest': {
                url: `http://pinterest.com/pin/create/button/?url=${PAGE_URL}`,
            },
            'pocket': {
                url: `https://getpocket.com/edit?url=${PAGE_URL}`,
            },
            'qzone': {
                url: `http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=${PAGE_URL}`,
            },
            'reddit': {
                url: `http://www.reddit.com/submit?url=${PAGE_URL}&title=${PAGE_TITLE}`,
            },
            'renren': {
                url: `http://widget.renren.com/dialog/share?resourceUrl=${PAGE_URL}&srcUrl=${PAGE_URL}&title=${PAGE_TITLE}`,
            },
            'skype': {
                url: `https://web.skype.com/share?url=${PAGE_URL}`,
            },
            'stumbleupon': {
                url: 'http://www.stumbleupon.com/submit?url=${PAGE_URL}&title=${PAGE_TITLE}',
            },
            'telegram': {
                url: `https://telegram.me/share/url?url=${PAGE_URL}&text=${PAGE_TITLE}`,
            },
            'tumblr': {
                url: `https://www.tumblr.com/widgets/share/tool?canonicalUrl=${PAGE_URL}&title=${PAGE_TITLE}`,
            },
            'twitter': {
                url: `https://twitter.com/intent/tweet?url=${PAGE_URL}&text=${PAGE_TITLE}`,
            },
            'vk': {
                url: `http://vk.com/share.php?url=${PAGE_URL}&title=${PAGE_TITLE}`,
            },
            'weibo': {
                url: `http://service.weibo.com/share/share.php?url=${PAGE_URL}&title=${PAGE_TITLE}`,
            },
            'whatsapp': {
                url: `whatsapp://send?text=${PAGE_TITLE}%20%0A${PAGE_URL}`,
            },
            'xing': {
                url: `https://www.xing.com/spi/shares/new?url=${PAGE_URL}`,
            },
        };
    }

    translate() {
        const languages = this.element.querySelectorAll('[data-google-lang]');
        if (languages.length > 0) {
            this.appendScript();
        }
    }

    appendScript() {
        const head = document.head;
        const script = document.createElement('script');
        script.src = 'https://translate.google.com/translate_a/element.js?cb=FLBTNTranslateInit';
        script.setAttribute('defer', '');
        head.appendChild(script);

        const styleElement = document.createElement('style');
        styleElement.textContent = '.skiptranslate {display: none !important;}body {top: 0 !important}';
        head.appendChild(styleElement);
    }

    static Translate() {
        const doc = document;
        const win = window;
        const deflang = doc.documentElement.lang.slice(0, 2);
        const lang = this.getLanguage(deflang);

        if (lang === deflang) {
            this.clearCookie();
        }

        const languages = Array.from(doc.querySelectorAll('[data-google-lang]'));

        if (!languages.length) {
            return false;
        }

        new google.translate.TranslateElement({
            pageLanguage: deflang,
        });

        languages.forEach((language) => {
            language.addEventListener('click', (e) => {
                e.preventDefault();
                let lang = language.getAttribute('data-google-lang');
                this.setCookie(lang);
                win.location.reload();
            });
        });
    }

    static getLanguage(lang) {
        const keyValue = document['cookie'].match('(^|;) ?googtrans=([^;]*)(;|$)');
        const cookieLang = keyValue ? keyValue[2].split('/')[2] : null;
        return (cookieLang !== undefined && cookieLang !== 'null') ? cookieLang : lang;
    }

    static clearCookie() {
        document.cookie = 'googtrans=null';
        document.cookie = 'googtrans=null; domain=' + document.domain;
    }

    static setCookie(lang) {
        document.cookie = 'googtrans=/auto/' + lang;
        document.cookie = 'googtrans=/auto/' + lang + '; domain=' + document.domain;
    }

    uncheckBtn() {
        if (!this.settings.uncheckedBtn) {
            return;
        }
        const id = this.settings.element;
        const element = this.element;
        const self = this;
        document.addEventListener('click', function (evt) {
            const parent = evt.target.closest('#' + id);
            if (parent !== element) {
                element.querySelector('input[type="checkbox"]').checked = false;
                self.hideButtons('show');
            }
        });
    }

    uncheckedSubBtn() {
        if (!this.settings.uncheckedSubBtn) {
            return;
        }
        const subBtns = [...this.element.querySelectorAll('ul a')];
        const self = this;
        subBtns.forEach((bnt) => {
            bnt.addEventListener('click', (e) => {
                this.element.querySelector('input[type="checkbox"]').checked = false;
                self.hideButtons('show');
            });
        });
    }

    hideButtons(action) {
        if (!this.settings.hideBtns) {
            return;
        }
        const allButtons = [...document.querySelectorAll('.flBtn')];
        const otherButtons = allButtons.filter(btn => btn !== this.element);
        if (action === 'hide') {
            otherButtons.forEach(btn => btn.classList.add('is-hidden'));
        } else {
            otherButtons.forEach(btn => btn.classList.remove('is-hidden'));
        }

    }

    check() {
        const checkBox = this.element.querySelector('input[type="checkbox"]');
        const element = this.element;
        const self = this;

        if (!checkBox) {
            return false;
        }

        function toggleButtons() {
            if (checkBox.checked) {
                element.classList.add('flBtn-stop');
                self.hideButtons('hide');
            } else {
                element.classList.remove('flBtn-stop');
                self.hideButtons('show');
            }
        }

        checkBox.addEventListener('change', toggleButtons);
        toggleButtons();
    }

    openMenu() {
        const flBtnMenu = document.querySelectorAll('[data-btn-type="content-menu"]');

        if (!flBtnMenu) {
            return false;
        }

        flBtnMenu.forEach((btn) => {
            btn.addEventListener('click', () => {
                const parent = btn.closest('.flBtn');
                const position = btn.getBoundingClientRect();
                const menuOrder = btn.getAttribute('data-btn-call-menu');
                const menu = document.querySelector('[data-btn-menu="' + menuOrder + '"]');
                const subMenu = btn.closest('ul');
                let typeFirst = false;
                if (subMenu && subMenu.querySelector('.flBtn-first')) {
                    typeFirst = subMenu.classList.contains('flBtn-first');
                }
                const winWidth = window.innerWidth;
                const winHeight = window.innerHeight;
                if (parent.classList.contains('flBtn-position-l')) {
                    menu.style.top = position.top + 'px';
                    menu.style.left = position.left + position.width + 10 + 'px';
                } else if (parent.classList.contains('flBtn-position-r')) {
                    menu.style.top = position.top + 'px';
                    menu.style.right = position.width + 40 + 'px';
                } else if (parent.classList.contains('flBtn-position-t')) {
                    menu.style.top = position.top + position.width + 10 + 'px';
                    menu.style.left = position.left + 'px';
                } else if (parent.classList.contains('flBtn-position-b')) {
                    menu.style.left = position.left + 'px';
                    menu.style.bottom = position.width + 40 + 'px';
                } else if (parent.classList.contains('flBtn-position-tr')) {
                    menu.style.top = typeFirst ? position.top + 'px' : position.bottom + 20 + 'px';
                    menu.style.right = typeFirst ? position.width + 40 + 'px' : winWidth - position.right + 'px';
                } else if (parent.classList.contains('flBtn-position-tl')) {
                    menu.style.top = typeFirst ? position.top + 'px' : position.bottom + 20 + 'px';
                    menu.style.left = typeFirst ? position.right + 10 + 'px' : position.left + 'px';
                } else if (parent.classList.contains('flBtn-position-bl')) {
                    menu.style.bottom = typeFirst ? winHeight - position.bottom + 'px' : winHeight - position.top + 20 + 'px';
                    menu.style.left = typeFirst ? position.right + 20 + 'px' : position.left + 'px';
                } else if (parent.classList.contains('flBtn-position-br')) {
                    menu.style.bottom = typeFirst ? winHeight - position.bottom + 'px' : winHeight - position.top + 20 + 'px';
                    menu.style.right = typeFirst ? winWidth - position.left + 20 + 'px' : winWidth - position.right + 'px';
                }

                menu.classList.remove('is-hidden');

            });
        });

    }

    closeMenu() {
        const flMenuClose = document.querySelectorAll('.flBtn_close');
        if (!flMenuClose) {
            return false;
        }

        flMenuClose.forEach((btn) => {
            btn.addEventListener('click', () => {
                const menu = btn.closest('.flBtn_menu-wrapper');
                menu.classList.add('is-hidden');
            });
        });
    }

    run() {
        this.activeLikeBtn();
        this.displayMenu();
        this.actionServices();
        this.shareServices();
        this.translate();
        this.uncheckBtn();
        this.uncheckedSubBtn();
        this.check();
        this.openMenu();
        this.closeMenu();
    }

    static initialize(options) {
        const flbtn = new FloatingButton(options);
        flbtn.run();
        return flbtn;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    for (let key in window) {
        if (key.indexOf('FloatingButton_') >= 0) {
            const val = window[key];
            new FloatingButton(val);
            FloatingButton.initialize(val);
        }
    }

});

function FLBTNTranslateInit() {
    FloatingButton.Translate();
}