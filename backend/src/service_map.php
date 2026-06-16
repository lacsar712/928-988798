<?php
require_once 'func.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>村社便民服务点导览 - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/service_map.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-gov-blue shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-geo-alt-fill me-2"></i>村社便民服务点导览
            </a>
            <span class="navbar-text text-white-50">
                <a href="index.php" class="text-white-50 text-decoration-none">
                    <i class="bi bi-house-door me-1"></i>返回首页
                </a>
            </span>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body py-3">
                <div class="row g-3 align-items-center">
                    <div class="col-md-3">
                        <label class="form-label fw-bold small mb-0">
                            <i class="bi bi-geo-fill me-1"></i>所属区域
                        </label>
                        <select class="form-select form-select-sm" id="townshipFilter" onchange="loadServicePoints()">
                            <option value="0">全部乡镇/街道</option>
                        </select>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label fw-bold small mb-0">
                            <i class="bi bi-tags-fill me-1"></i>服务事项筛选
                        </label>
                        <div id="tagFilters" class="d-flex flex-wrap gap-2 mt-1">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small mb-0">
                            <i class="bi bi-search me-1"></i>搜索
                        </label>
                        <input type="text" class="form-control form-control-sm" id="keywordInput" placeholder="输入关键词..." onkeyup="if(event.key==='Enter')loadServicePoints()">
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0 fw-bold text-gov-blue">
                            <i class="bi bi-map-fill me-2"></i>行政区划示意图
                        </h5>
                        <div class="text-muted small">
                                共 <span id="mapPointCount" class="fw-bold text-gov-blue">0</span> 个服务点
                            </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="map-container" id="mapContainer">
                            <svg id="mapSvg" viewBox="0 0 600 500" preserveAspectRatio="xMidYMid meet">
                                <defs>
                                    <pattern id="grid" width="30" height="30" patternUnits="userSpaceOnUse">
                                        <path d="M 30 0 L 0 0 0 30" fill="none" stroke="#e9ecef" stroke-width="1"/>
                                    </pattern>
                                    <filter id="shadow" x="-50%" y="-50%" width="200%" height="200%">
                                        <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity="0.3"/>
                                    </filter>
                                </defs>
                                <rect width="100%" height="100%" fill="url(#grid)"/>
                                <g id="townshipBoundaries"></g>
                                <g id="pointsLayer"></g>
                                <g id="highlightLayer"></g>
                            </svg>
                            <div class="map-legend">
                                <span class="legend-item"><span class="legend-dot" style="background:#004d99;"></span>服务点</span>
                                <span class="legend-item"><span class="legend-dot legend-dot-active"></span>当前选中</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0 fw-bold text-gov-blue">
                        <i class="bi bi-list-ul me-2"></i>服务点列表
                        </h5>
                        <div class="text-muted small">
                            共 <span id="listCount" class="fw-bold text-gov-blue">0</span> 条记录
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="service-list" id="serviceList">
                            <div class="text-center py-5 text-muted">
                                <div class="spinner-border spinner-border-sm me-2"></div>加载中...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 rounded-3 shadow-lg">
                <div class="modal-header bg-gov-blue text-white">
                    <h5 class="modal-title fw-bold" id="detailModalTitle">
                        <i class="bi bi-geo-alt-fill me-2"></i>服务点详情
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detailModalBody">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/service_map.js"></script>
</body>
</html>
