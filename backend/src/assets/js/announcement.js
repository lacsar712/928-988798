(function() {
    const Announcement = {
        apiBase: 'announcement_api.php',
        
        init: function() {
            this.loadMarquee();
            this.loadFloat();
            this.loadBottomBar();
        },

        api: function(action, data, method) {
            method = method || 'POST';
            const formData = new URLSearchParams();
            formData.append('action', action);
            for (let key in data) {
                formData.append(key, data[key]);
            }
            
            if (method === 'GET') {
                let url = this.apiBase + '?action=' + action;
                for (let key in data) {
                    url += '&' + key + '=' + encodeURIComponent(data[key]);
                }
                return fetch(url).then(r => r.json());
            }
            
            return fetch(this.apiBase, {
                method: method,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(r => r.json());
        },

        reportClick: function(id) {
            if (!id) return;
            this.api('report_click', { id }, 'POST').catch(() => {});
        },

        isDismissedToday: function(id) {
            const key = 'announcement_dismissed_' + id;
            const today = new Date().toDateString();
            return localStorage.getItem(key) === today;
        },

        dismissToday: function(id) {
            const key = 'announcement_dismissed_' + id;
            const today = new Date().toDateString();
            localStorage.setItem(key, today);
        },

        loadMarquee: function() {
            this.api('get_announcements', { position: 1 }, 'GET').then(res => {
                if (res.code === 200 && res.data && res.data.length > 0) {
                    this.renderMarquee(res.data);
                }
            });
        },

        renderMarquee: function(items) {
            const undismissedItems = items.filter(item => !this.isDismissedToday(item.id));
            if (undismissedItems.length === 0) return;

            const firstItem = undismissedItems[0];
            
            const container = document.createElement('div');
            container.className = 'announcement-marquee';
            container.style.background = firstItem.bg_color;
            container.style.color = firstItem.text_color;
            container.id = 'announcement-marquee-' + firstItem.id;

            const content = document.createElement('div');
            content.className = 'announcement-marquee-content';
            
            let html = '<i class="bi bi-megaphone-fill me-3"></i>';
            undismissedItems.forEach((item, idx) => {
                const itemText = item.content ? (item.title + ' - ' + item.content) : item.title;
                if (item.link_url) {
                    html += '<a href="' + item.link_url + '" class="announcement-marquee-link" data-id="' + item.id + '">' + itemText + '</a>';
                } else {
                    html += '<span>' + itemText + '</span>';
                }
                if (idx < undismissedItems.length - 1) {
                    html += '&nbsp;&nbsp;&nbsp;&nbsp;◆&nbsp;&nbsp;&nbsp;&nbsp;';
                }
            });
            
            content.innerHTML = html;
            container.appendChild(content);

            if (firstItem.can_close) {
                const closeBtn = document.createElement('span');
                closeBtn.className = 'announcement-marquee-close';
                closeBtn.innerHTML = '&times;';
                closeBtn.onclick = (e) => {
                    e.stopPropagation();
                    undismissedItems.forEach(item => this.dismissToday(item.id));
                    container.style.display = 'none';
                };
                container.appendChild(closeBtn);
            }

            const navbar = document.querySelector('.navbar');
            if (navbar) {
                navbar.parentNode.insertBefore(container, navbar.nextSibling);
            } else {
                document.body.insertBefore(container, document.body.firstChild);
            }

            container.querySelectorAll('.announcement-marquee-link').forEach(link => {
                link.addEventListener('click', () => {
                    const id = parseInt(link.getAttribute('data-id'));
                    this.reportClick(id);
                });
            });
        },

        loadFloat: function() {
            this.api('get_announcements', { position: 2 }, 'GET').then(res => {
                if (res.code === 200 && res.data && res.data.length > 0) {
                    const undismissedItems = res.data.filter(item => !this.isDismissedToday(item.id));
                    if (undismissedItems.length > 0) {
                        this.renderFloats(undismissedItems);
                    }
                }
            });
        },

        renderFloats: function(items) {
            items.forEach((item, idx) => {
                setTimeout(() => {
                    this.renderFloat(item, idx);
                }, idx * 500);
            });
        },

        renderFloat: function(item, index) {
            const container = document.createElement('div');
            container.className = 'announcement-float';
            container.style.background = item.bg_color;
            container.style.color = item.text_color;
            container.id = 'announcement-float-' + item.id;

            const offsetX = 20 + (index % 3) * 20;
            const offsetY = 100 + index * 30;
            container.style.top = offsetY + 'px';
            container.style.right = offsetX + 'px';

            const header = document.createElement('div');
            header.className = 'announcement-float-header';
            
            const titleSpan = document.createElement('span');
            titleSpan.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>' + item.title;
            header.appendChild(titleSpan);

            if (item.can_close) {
                const closeBtn = document.createElement('span');
                closeBtn.className = 'announcement-float-close';
                closeBtn.innerHTML = '&times;';
                closeBtn.onclick = (e) => {
                    e.stopPropagation();
                    this.dismissToday(item.id);
                    container.style.animation = 'none';
                    container.style.opacity = '0';
                    container.style.transform = 'scale(0.9) translateY(-20px)';
                    container.style.transition = 'all 0.3s ease-out';
                    setTimeout(() => container.remove(), 300);
                };
                header.appendChild(closeBtn);
            }

            container.appendChild(header);

            const body = document.createElement('div');
            body.className = 'announcement-float-body';

            if (item.content) {
                const contentDiv = document.createElement('div');
                contentDiv.className = 'announcement-float-content';
                contentDiv.textContent = item.content;
                body.appendChild(contentDiv);
            }

            if (item.link_url) {
                const link = document.createElement('a');
                link.className = 'announcement-float-link';
                link.href = item.link_url;
                link.style.background = item.text_color;
                link.style.color = item.bg_color;
                link.textContent = '查看详情';
                link.onclick = () => {
                    this.reportClick(item.id);
                };
                body.appendChild(link);
            }

            container.appendChild(body);
            document.body.appendChild(container);

            this.enableDrag(container, header);
        },

        enableDrag: function(element, handle) {
            let isDragging = false;
            let startX, startY, initialX, initialY;

            handle.addEventListener('mousedown', function(e) {
                if (e.target.classList.contains('announcement-float-close')) return;
                isDragging = true;
                startX = e.clientX;
                startY = e.clientY;
                initialX = element.offsetLeft;
                initialY = element.offsetTop;
                element.classList.add('announcement-drag-ghost');
                element.style.transition = 'none';
                e.preventDefault();
            });

            document.addEventListener('mousemove', function(e) {
                if (!isDragging) return;
                const dx = e.clientX - startX;
                const dy = e.clientY - startY;
                element.style.right = 'auto';
                element.style.left = (initialX + dx) + 'px';
                element.style.top = (initialY + dy) + 'px';
            });

            document.addEventListener('mouseup', function() {
                if (isDragging) {
                    isDragging = false;
                    element.classList.remove('announcement-drag-ghost');
                    element.style.transition = '';
                }
            });

            handle.addEventListener('touchstart', function(e) {
                if (e.target.classList.contains('announcement-float-close')) return;
                isDragging = true;
                const touch = e.touches[0];
                startX = touch.clientX;
                startY = touch.clientY;
                initialX = element.offsetLeft;
                initialY = element.offsetTop;
                element.classList.add('announcement-drag-ghost');
                element.style.transition = 'none';
            }, { passive: true });

            document.addEventListener('touchmove', function(e) {
                if (!isDragging) return;
                const touch = e.touches[0];
                const dx = touch.clientX - startX;
                const dy = touch.clientY - startY;
                element.style.right = 'auto';
                element.style.left = (initialX + dx) + 'px';
                element.style.top = (initialY + dy) + 'px';
            }, { passive: true });

            document.addEventListener('touchend', function() {
                if (isDragging) {
                    isDragging = false;
                    element.classList.remove('announcement-drag-ghost');
                    element.style.transition = '';
                }
            });
        },

        loadBottomBar: function() {
            this.api('get_announcements', { position: 3 }, 'GET').then(res => {
                if (res.code === 200 && res.data && res.data.length > 0) {
                    const undismissedItems = res.data.filter(item => !this.isDismissedToday(item.id));
                    if (undismissedItems.length > 0) {
                        this.renderBottomBar(undismissedItems);
                    }
                }
            });
        },

        renderBottomBar: function(items) {
            const firstItem = items[0];

            const container = document.createElement('div');
            container.className = 'announcement-bottombar';
            container.style.background = firstItem.bg_color;
            container.style.color = firstItem.text_color;
            container.id = 'announcement-bottombar';

            const icon = document.createElement('i');
            icon.className = 'bi bi-info-circle';
            container.appendChild(icon);

            const content = document.createElement('div');
            content.className = 'announcement-bottombar-content';

            let html = '';
            items.forEach((item, idx) => {
                if (item.link_url) {
                    html += '<a href="' + item.link_url + '" class="announcement-bottombar-link" data-id="' + item.id + '">' + item.title + '</a>';
                } else {
                    html += '<span>' + item.title + '</span>';
                }
                if (idx < items.length - 1) {
                    html += '&nbsp;&nbsp;|&nbsp;&nbsp;';
                }
            });
            content.innerHTML = html;
            container.appendChild(content);

            if (firstItem.can_close) {
                const closeBtn = document.createElement('span');
                closeBtn.className = 'announcement-bottombar-close';
                closeBtn.innerHTML = '&times;';
                closeBtn.onclick = (e) => {
                    e.stopPropagation();
                    items.forEach(item => this.dismissToday(item.id));
                    container.style.animation = 'none';
                    container.style.transition = 'all 0.3s ease-out';
                    container.style.transform = 'translateY(100%)';
                    container.style.opacity = '0';
                    document.body.classList.remove('announcement-bottombar-open');
                    setTimeout(() => container.remove(), 300);
                };
                container.appendChild(closeBtn);
            }

            document.body.appendChild(container);
            document.body.classList.add('announcement-bottombar-open');

            container.querySelectorAll('.announcement-bottombar-link').forEach(link => {
                link.addEventListener('click', () => {
                    const id = parseInt(link.getAttribute('data-id'));
                    this.reportClick(id);
                });
            });
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            Announcement.init();
        });
    } else {
        Announcement.init();
    }

    window.Announcement = Announcement;
})();
