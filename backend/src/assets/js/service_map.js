let townships = [];
let tags = [];
let servicePoints = [];
let selectedTags = [];
let activePointId = null;

function apiGet(action, data = {}) {
    let url = 'service_map_api.php?action=' + action;
    for (let key in data) {
        url += '&' + key + '=' + encodeURIComponent(data[key]);
    }
    return fetch(url).then(r => r.json());
}

function initTownshipFilter() {
    apiGet('get_townships').then(res => {
        if (res.code === 200) {
            townships = res.data;
            const select = document.getElementById('townshipFilter');
            townships.forEach(t => {
                const option = document.createElement('option');
                option.value = t.id;
                option.textContent = t.name + (t.type === 2 ? '（街道）' : '（乡镇）');
                select.appendChild(option);
            });
            drawTownshipBoundaries();
        }
    });
}

function initTagFilters() {
    apiGet('get_tags').then(res => {
        if (res.code === 200) {
            tags = res.data;
            const container = document.getElementById('tagFilters');
            tags.forEach(t => {
                const tag = document.createElement('span');
                tag.className = 'tag-filter';
                tag.dataset.tagId = t.id;
                tag.innerHTML = `<i class="bi ${t.icon || 'bi-tag'}"></i>${t.name}`;
                tag.onclick = () => toggleTag(t.id, tag);
                container.appendChild(tag);
            });
        }
    });
}

function toggleTag(tagId, element) {
    const idx = selectedTags.indexOf(tagId);
    if (idx > -1) {
        selectedTags.splice(idx, 1);
        element.classList.remove('active');
    } else {
        selectedTags.push(tagId);
        element.classList.add('active');
    }
    loadServicePoints();
}

function loadServicePoints() {
    const townshipId = document.getElementById('townshipFilter').value;
    const keyword = document.getElementById('keywordInput').value.trim();
    
    const params = {};
    if (townshipId > 0) params.township_id = townshipId;
    if (selectedTags.length > 0) params.tag_ids = selectedTags.join(',');
    if (keyword) params.keyword = keyword;

    apiGet('get_service_points', params).then(res => {
        if (res.code === 200) {
            servicePoints = res.data;
            document.getElementById('mapPointCount').textContent = servicePoints.length;
            document.getElementById('listCount').textContent = servicePoints.length;
            renderPoints();
            renderList();
        }
    });
}

function drawTownshipBoundaries() {
    const svg = document.getElementById('mapSvg');
    const boundaries = document.getElementById('townshipBoundaries');
    boundaries.innerHTML = '';

    const townshipAreas = [
        { id: 1, name: '城关镇', path: 'M 150,50 L 350,50 L 350,250 L 150,250 Z', cx: 250, cy: 150 },
        { id: 2, name: '河东镇', path: 'M 350,50 L 550,50 L 550,250 L 350,250 Z', cx: 450, cy: 150 },
        { id: 3, name: '河西镇', path: 'M 50,50 L 150,50 L 150,350 L 50,350 Z', cx: 100, cy: 200 },
        { id: 4, name: '南山镇', path: 'M 350,250 L 550,250 L 550,450 L 350,450 Z', cx: 450, cy: 350 },
        { id: 5, name: '北坝镇', path: 'M 150,50 L 350,50 L 350,120 L 150,120 Z', cx: 250, cy: 85 },
        { id: 6, name: '东城街道', path: 'M 350,180 L 480,180 L 480,300 L 350,300 Z', cx: 415, cy: 240 },
        { id: 7, name: '西城街道', path: 'M 150,250 L 280,250 L 280,380 L 150,380 Z', cx: 215, cy: 315 },
        { id: 8, name: '南城街道', path: 'M 280,300 L 420,300 L 420,450 L 280,450 Z', cx: 350, cy: 375 },
        { id: 9, name: '北城街道', path: 'M 150,120 L 300,120 L 300,200 L 150,200 Z', cx: 225, cy: 160 }
    ];

    townshipAreas.forEach(area => {
        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('d', area.path);
        path.setAttribute('class', 'township-boundary');
        boundaries.appendChild(path);

        const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        text.setAttribute('x', area.cx);
        text.setAttribute('y', area.cy);
        text.setAttribute('class', 'township-label');
        text.setAttribute('text-anchor', 'middle');
        text.textContent = area.name;
        boundaries.appendChild(text);
    });
}

function renderPoints() {
    const svg = document.getElementById('mapSvg');
    const pointsLayer = document.getElementById('pointsLayer');
    pointsLayer.innerHTML = '';

    const tooltip = document.createElement('div');
    tooltip.className = 'point-tooltip';
    tooltip.id = 'pointTooltip';
    document.getElementById('mapContainer').appendChild(tooltip);

    servicePoints.forEach(point => {
        const g = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        g.setAttribute('class', 'map-point');
        g.setAttribute('data-point-id', point.id);
        
        const cx = point.coord_x * 5 + 25;
        const cy = point.coord_y * 4.5 + 25;

        const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        circle.setAttribute('cx', cx);
        circle.setAttribute('cy', cy);
        circle.setAttribute('r', 7);
        circle.setAttribute('fill', '#004d99');
        circle.setAttribute('stroke', 'white');
        circle.setAttribute('stroke-width', 2);

        g.appendChild(circle);

        g.addEventListener('mouseenter', (e) => {
            const rect = svg.getBoundingClientRect();
            const containerRect = document.getElementById('mapContainer').getBoundingClientRect();
            const x = (cx / 600) * rect.width + (rect.left - containerRect.left);
            const y = (cy / 500) * rect.height + (rect.top - containerRect.top);
            
            tooltip.innerHTML = `<strong>${point.name}</strong><br>${point.township_name}`;
            tooltip.style.left = x + 'px';
            tooltip.style.top = y + 'px';
            tooltip.classList.add('visible');
        });

        g.addEventListener('mouseleave', () => {
            tooltip.classList.remove('visible');
        });

        g.addEventListener('click', () => {
            selectPoint(point.id);
            showDetail(point.id);
        });

        pointsLayer.appendChild(g);
    });
}

function renderList() {
    const list = document.getElementById('serviceList');
    
    if (servicePoints.length === 0) {
        list.innerHTML = `
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <p>暂无符合条件的服务点</p>
                <p class="small">请尝试调整筛选条件</p>
            </div>
        `;
        return;
    }

    list.innerHTML = servicePoints.map(point => `
        <div class="service-item" data-point-id="${point.id}" onclick="selectPoint(${point.id})">
            <div class="service-item-header">
                <span class="service-item-title">${point.name}</span>
                <span class="service-item-distance">
                    <i class="bi bi-geo-alt me-1"></i>${point.distance} km
                </span>
            </div>
            <div class="service-item-info">
                <i class="bi bi-geo-fill"></i>${point.address || '暂无地址'}
            </div>
            <div class="service-item-info">
                <i class="bi bi-telephone"></i>${point.phone || '暂无电话'}
            </div>
            <div class="service-item-info">
                <i class="bi bi-clock"></i>${point.open_time || '暂无开放时间'}
            </div>
            <div class="service-item-info">
                <i class="bi bi-building"></i>${point.township_name}
            </div>
            <div class="service-item-tags">
                ${point.tags.map(tag => `
                    <span class="service-tag" style="background: ${tag.color};">
                        <i class="bi ${tag.icon || 'bi-tag'} me-1"></i>${tag.name}
                    </span>
                `).join('')}
            </div>
        </div>
    `).join('');
}

function selectPoint(pointId) {
    activePointId = pointId;

    document.querySelectorAll('.map-point').forEach(el => {
        if (parseInt(el.dataset.pointId) === pointId) {
            el.classList.add('active');
        } else {
            el.classList.remove('active');
        }
    });

    document.querySelectorAll('.service-item').forEach(el => {
        if (parseInt(el.dataset.pointId) === pointId) {
            el.classList.add('active');
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            el.classList.remove('active');
        }
    });
}

function showDetail(pointId) {
    apiGet('get_service_point_detail', { id: pointId }).then(res => {
        if (res.code === 200) {
            const point = res.data;
            const body = document.getElementById('detailModalBody');
            
            body.innerHTML = `
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="detail-section">
                            <div class="detail-label">服务点名称</div>
                            <div class="detail-value fw-bold">${point.name}</div>
                        </div>
                        <div class="detail-section">
                            <div class="detail-label">所属区域</div>
                            <div class="detail-value">
                                <span class="badge bg-light text-dark">${point.township_name}</span>
                            </div>
                        </div>
                        <div class="detail-section">
                            <div class="detail-label">详细地址</div>
                            <div class="detail-value">
                                <i class="bi bi-geo-fill text-gov-blue me-2"></i>${point.address || '暂无'}
                            </div>
                        </div>
                        <div class="detail-section">
                            <div class="detail-label">联系电话</div>
                            <div class="detail-value">
                                <i class="bi bi-telephone text-gov-blue me-2"></i>${point.phone || '暂无'}
                            </div>
                        </div>
                        <div class="detail-section">
                            <div class="detail-label">开放时间</div>
                            <div class="detail-value">
                                <i class="bi bi-clock text-gov-blue me-2"></i>${point.open_time || '暂无'}
                            </div>
                        </div>
                        <div class="detail-section">
                            <div class="detail-label">距离</div>
                            <div class="detail-value">
                                <i class="bi bi-signpost text-gov-blue me-2"></i>${point.distance} 公里
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-section">
                            <div class="detail-label">地图坐标</div>
                            <div class="detail-value mb-2">
                                <span class="coord-badge">X: ${point.coord_x}</span>
                                <span class="coord-badge">Y: ${point.coord_y}</span>
                            </div>
                            <div class="detail-map-preview">
                                <svg width="180" height="160" viewBox="0 0 200 180">
                                    <rect width="200" height="180" fill="#fafbfc" stroke="#e9ecef"/>
                                    <g stroke="#e9ecef" stroke-width="1">
                                        <line x1="40" y1="0" x2="40" y2="180"/>
                                        <line x1="80" y1="0" x2="80" y2="180"/>
                                        <line x1="120" y1="0" x2="120" y2="180"/>
                                        <line x1="160" y1="0" x2="160" y2="180"/>
                                        <line x1="0" y1="36" x2="200" y2="36"/>
                                        <line x1="0" y1="72" x2="200" y2="72"/>
                                        <line x1="0" y1="108" x2="200" y2="108"/>
                                        <line x1="0" y1="144" x2="200" y2="144"/>
                                    </g>
                                    <circle cx="${point.coord_x * 1.8 + 10}" cy="${point.coord_y * 1.6 + 10}" r="10" fill="#dc3545" stroke="white" stroke-width="3"/>
                                </svg>
                            </div>
                        </div>
                        <div class="detail-section">
                            <div class="detail-label">可办理事项</div>
                            <div class="d-flex flex-wrap gap-2">
                                ${point.tags.map(tag => `
                                    <span class="service-tag" style="background: ${tag.color};">
                                        <i class="bi ${tag.icon || 'bi-tag'} me-1"></i>${tag.name}
                                    </span>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                </div>
            `;

            const modal = new bootstrap.Modal(document.getElementById('detailModal'));
            modal.show();
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initTownshipFilter();
    initTagFilters();
    loadServicePoints();
});
