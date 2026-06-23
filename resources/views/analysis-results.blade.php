<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ContentAnalyzer AI - Hasil Analisis {{ $profile->username }}</title>
    
    <!-- CSS & JS Dependencies -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            body {
                background: #ffffff !important;
                background-image: none !important;
                color: #111827 !important;
            }
            .back-link,
            .btn,
            .alert,
            #loadingOverlay,
            .loading-overlay,
            form,
            .videos-table th:last-child,
            .videos-table td:last-child,
            header {
                display: none !important;
            }
            .container {
                max-width: 100% !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .glass-card {
                background: #ffffff !important;
                color: #111827 !important;
                border: 1px solid #d1d5db !important;
                box-shadow: none !important;
                backdrop-filter: none !important;
                -webkit-backdrop-filter: none !important;
                margin-bottom: 2rem !important;
                padding: 1.5rem !important;
                page-break-inside: avoid;
                break-inside: avoid;
            }
            h2, h3, h4, th, td, strong, span, .metric-card-value, p {
                color: #111827 !important;
            }
            .metric-card {
                background: #f9fafb !important;
                border: 1px solid #d1d5db !important;
            }
            .metric-card-value {
                font-size: 1.5rem !important;
            }
            .ai-report {
                color: #111827 !important;
            }
            .videos-table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            .videos-table th {
                background: #f3f4f6 !important;
                color: #111827 !important;
                border-bottom: 2px solid #d1d5db !important;
            }
            .videos-table td {
                border-bottom: 1px solid #e5e7eb !important;
                color: #374151 !important;
            }
            .badge {
                border: 1px solid #4b5563 !important;
                color: #374151 !important;
                background: transparent !important;
            }
            .chart-wrapper {
                border: 1px solid #d1d5db !important;
                padding: 1rem !important;
                background: #ffffff !important;
            }
            .ai-section {
                display: block !important;
            }
            .ai-section > div {
                margin-bottom: 2rem !important;
            }
        }
    </style>
    <script src="{{ asset('js/chart.umd.min.js') }}"></script>
</head>
<body>

    <div class="container" style="padding-top: 2rem;">
        
        <!-- Back Button -->
        <a href="{{ route('dashboard') }}" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
        </a>

        <!-- Header Grid -->
        <div class="glass-card results-header-grid">
            <div class="creator-profile-info">
                <img src="{{ $profile->avatar_url }}" alt="{{ $profile->username }}" class="creator-avatar" onerror="this.src='https://ui-avatars.com/api/?name={{ $profile->username }}'">
                <div class="creator-details">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <h2>{{ $profile->username }}</h2>
                        @if($profile->platform == 'tiktok')
                            <span class="badge badge-tiktok"><i class="fa-brands fa-tiktok"></i> TikTok</span>
                        @elseif($profile->platform == 'instagram')
                            <span class="badge badge-instagram"><i class="fa-brands fa-instagram"></i> Instagram</span>
                        @elseif($profile->platform == 'youtube')
                            <span class="badge badge-youtube"><i class="fa-brands fa-youtube"></i> YouTube</span>
                        @endif
                    </div>
                    <p>
                        <i class="fa-regular fa-clock"></i> 
                        Dinalisis pada: {{ $profile->analyzed_at ? $profile->analyzed_at->format('d M Y, H:i') : now()->format('d M Y, H:i') }} WIB
                    </p>
                </div>
            </div>
            <div style="display: flex; gap: 0.75rem; align-items: center; justify-content: center; flex-wrap: wrap;">
                <a href="{{ route('analysis.export', $profile->id) }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.5rem; justify-content: center;">
                    <i class="fa-solid fa-file-csv"></i> Ekspor CSV Video
                </a>
                <button onclick="window.print()" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; justify-content: center; cursor: pointer; border: none; min-width: 180px;">
                    <i class="fa-solid fa-file-pdf"></i> Cetak Laporan PDF
                </button>
            </div>
        </div>

        <!-- Alerts -->
        @if(isset($profile->aiAnalysis) && $profile->aiAnalysis->raw_analysis && str_contains($profile->aiAnalysis->raw_analysis, 'analisis simulasi'))
            <div class="alert alert-info">
                <i class="fa-solid fa-circle-info"></i>
                <span>Menampilkan analisis simulasi. Masukkan <strong>GEMINI_API_KEY</strong> di file <code>.env</code> untuk mengaktifkan analisis bertenaga AI asli secara real-time.</span>
            </div>
        @endif

        <!-- Metrics Row -->
        <div class="metrics-row" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
            <div class="glass-card metric-card">
                <div class="metric-card-label">Total Followers</div>
                <div class="metric-card-value">
                    {{ number_format($profile->followers) }}
                </div>
            </div>
            <div class="glass-card metric-card">
                <div class="metric-card-label">Total Views Teranalisis</div>
                <div class="metric-card-value">
                    {{ number_format($totalViews) }}
                </div>
            </div>
            <div class="glass-card metric-card">
                <div class="metric-card-label">Rata-rata Engagement Rate</div>
                <div class="metric-card-value">
                    <span class="@if($avgEr >= 5) engagement-high @elseif($avgEr >= 2) engagement-medium @else engagement-low @endif">
                        {{ number_format($avgEr, 2) }}%
                    </span>
                </div>
            </div>
            <div class="glass-card metric-card">
                <div class="metric-card-label">Rerata Likes / Video</div>
                <div class="metric-card-value">
                    {{ number_format($avgLikes) }}
                </div>
            </div>
            <div class="glass-card metric-card">
                <div class="metric-card-label">Rerata Komentar / Video</div>
                <div class="metric-card-value">
                    {{ number_format($avgComments) }}
                </div>
            </div>
        </div>

        <!-- AI Insights & Recommendations -->
        @if($profile->aiAnalysis)
            <div class="ai-section">
                <!-- AI Narrative Report -->
                <div class="glass-card">
                    <h2 style="font-size: 1.35rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; color: #fff; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                        <i class="fa-solid fa-brain" style="color: var(--accent-indigo)"></i>
                        Laporan Analisis Strategis AI
                    </h2>
                    
                    <div id="aiMarkdownReport" class="ai-report">
                        <!-- Markdown will be rendered here by JS -->
                        <div style="display: none;" id="rawMarkdown">{{ $profile->aiAnalysis->raw_analysis }}</div>
                    </div>
                </div>

                <!-- Structured recommendations & quick details -->
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <!-- Quick Niche/Sentiment Card -->
                    <div class="glass-card" style="padding: 1.5rem;">
                        <h3 style="font-size: 1.1rem; margin-bottom: 1rem; color: #fff;">Ringkasan Profil</h3>
                        
                        <div style="margin-bottom: 1rem;">
                            <div style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase;">Niche / Kategori</div>
                            <div style="font-weight: 700; font-size: 1.2rem; color: var(--accent-indigo); margin-top: 0.15rem;">
                                {{ $profile->niche ?? ($profile->aiAnalysis->niche ?? 'Lifestyle') }}
                            </div>
                        </div>

                        @if(!empty($profile->product_offered))
                            <div style="margin-bottom: 1rem;">
                                <div style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase;">Produk Ditawarkan</div>
                                <div style="font-weight: 700; font-size: 1.1rem; color: var(--accent-purple); margin-top: 0.15rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <i class="fa-solid fa-bag-shopping"></i>
                                    {{ $profile->product_offered }}
                                </div>
                            </div>
                        @endif

                        @if(!empty($profile->aiAnalysis->creator_character))
                            <div style="margin-bottom: 1rem;">
                                <div style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase;">Karakter / Persona Kreator</div>
                                <div style="font-weight: 500; font-size: 0.9rem; color: #fff; margin-top: 0.25rem; line-height: 1.45; display: flex; flex-direction: column; gap: 0.25rem;">
                                    <span style="font-weight: 700; color: #fbbf24; display: flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; text-transform: uppercase;">
                                        <i class="fa-solid fa-user-gear"></i>
                                        Persona AI Terdeteksi
                                    </span>
                                    <span>{{ $profile->aiAnalysis->creator_character }}</span>
                                </div>
                            </div>
                        @endif

                        <div>
                            <div style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase;">Sentimen Konten</div>
                            <div style="font-weight: 700; font-size: 1.2rem; color: var(--success); margin-top: 0.15rem; display: flex; align-items: center; gap: 0.4rem;">
                                <i class="fa-regular fa-face-smile"></i>
                                {{ $profile->aiAnalysis->sentiment ?? 'Positive' }}
                            </div>
                        </div>
                    </div>

                    <!-- Key Actions Card -->
                    <div class="glass-card" style="padding: 1.5rem;">
                        <h3 style="font-size: 1.1rem; margin-bottom: 1rem; color: #fff;">Rekomendasi Cepat AI</h3>
                        <div class="ai-recommendation-list">
                            @if(is_array($profile->aiAnalysis->recommendations))
                                @foreach($profile->aiAnalysis->recommendations as $rec)
                                    <div class="recommendation-item">
                                        <i class="fa-solid fa-circle-check"></i>
                                        <div style="font-size: 0.9rem; color: var(--text-primary);">{{ $rec }}</div>
                                    </div>
                                @endforeach
                            @else
                                <div class="recommendation-item">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <div style="font-size: 0.9rem; color: var(--text-primary);">Optimalkan konsistensi video pendek.</div>
                                </div>
                                <div class="recommendation-item">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <div style="font-size: 0.9rem; color: var(--text-primary);">Gunakan audio/soundtrack tren untuk meningkatkan reach.</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Hashtag Terpopuler Card -->
                    <div class="glass-card" style="padding: 1.5rem;">
                        <h3 style="font-size: 1.1rem; margin-bottom: 1rem; color: #fff; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-hashtag" style="color: var(--accent-indigo);"></i>
                            Hashtag Terpopuler
                        </h3>
                        @if(empty($popularHashtags))
                            <p style="color: var(--text-secondary); font-size: 0.85rem; text-align: center;">Tidak ada hashtag terdeteksi dalam caption video.</p>
                        @else
                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                @foreach($popularHashtags as $hash)
                                    <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(255, 255, 255, 0.02); padding: 0.6rem 0.875rem; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.03);">
                                        <span style="font-weight: 600; color: #fff; font-size: 0.9rem;">
                                            {{ $hash['tag'] }}
                                        </span>
                                        <span style="font-size: 0.75rem; background: rgba(99, 102, 241, 0.15); color: var(--accent-indigo); padding: 0.2rem 0.5rem; border-radius: 12px; font-weight: 700;">
                                            {{ $hash['count'] }}x digunakan
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Charts Section -->
        <div class="charts-grid">
            <div class="glass-card">
                <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-chart-line" style="color: var(--accent-indigo)"></i>
                    Tren Performa Engagement Rate per Video (%)
                </h2>
                <div class="chart-wrapper">
                    <canvas id="erTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Best Video Feature -->
        @if($topVideo)
            <div class="glass-card" style="margin-bottom: 3rem; background: linear-gradient(135deg, rgba(30, 41, 59, 0.8), rgba(99, 102, 241, 0.15)); border-color: rgba(99, 102, 241, 0.3);">
                <div style="display: grid; grid-template-columns: 1fr; gap: 2rem; align-items: center;">
                    <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
                        <img src="{{ $topVideo->thumbnail_url }}" alt="Top Video" onerror="this.src='{{ $profile->avatar_url }}'" style="width: 100px; height: 130px; object-fit: cover; border-radius: 10px; border: 2px solid var(--accent-indigo); box-shadow: 0 0 15px rgba(99, 102, 241, 0.4);">
                        <div style="flex: 1; min-width: 250px;">
                            <div style="background: rgba(99, 102, 241, 0.2); color: #fff; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.75rem; border-radius: 20px; text-transform: uppercase; margin-bottom: 0.75rem; border: 1px solid rgba(99, 102, 241, 0.4);">
                                <i class="fa-solid fa-crown" style="color: #fbbf24;"></i> Video Performa Terbaik (Top Video)
                            </div>
                            <h3 style="font-size: 1.15rem; margin-bottom: 0.5rem; line-height: 1.5; color: #fff;">
                                "{{ $topVideo->caption }}"
                            </h3>
                            <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0.75rem;">
                                Diposting pada: {{ $topVideo->post_date->format('d M Y, H:i') }}
                            </p>
                            <div style="display: flex; gap: 1.5rem; font-size: 0.9rem;">
                                <div><i class="fa-regular fa-eye" style="color: var(--text-secondary)"></i> <strong>{{ number_format($topVideo->views) }}</strong> Views</div>
                                <div><i class="fa-regular fa-heart" style="color: var(--danger)"></i> <strong>{{ number_format($topVideo->likes) }}</strong> Likes</div>
                                <div><i class="fa-regular fa-comment" style="color: var(--accent-indigo)"></i> <strong>{{ number_format($topVideo->comments) }}</strong> Komentar</div>
                                <div><i class="fa-solid fa-chart-simple" style="color: var(--success)"></i> <strong>{{ number_format($topVideo->engagement_rate, 2) }}%</strong> ER</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- 3-Month Content Planner Section -->
        @if($profile->aiAnalysis && !empty($profile->aiAnalysis->content_plan))
            <div class="glass-card" style="margin-bottom: 3rem; border-color: rgba(168, 85, 247, 0.25);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                    <h2 style="font-size: 1.25rem; display: flex; align-items: center; gap: 0.5rem; color: #fff;">
                        <i class="fa-regular fa-calendar-check" style="color: var(--accent-purple);"></i>
                        Rencana & Kalender Konten AI (3 Bulan)
                    </h2>
                    <a href="{{ route('analysis.export-planner', $profile->id) }}" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem; border-color: rgba(168, 85, 247, 0.4); background: rgba(168, 85, 247, 0.1); color: #e9d5ff;">
                        <i class="fa-solid fa-file-csv"></i> Unduh Content Planner (Excel)
                    </a>
                </div>

                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem;">
                    Rencana konten 12 minggu ini dirancang khusus oleh AI untuk mencerminkan niche <strong>{{ $profile->aiAnalysis->niche }}</strong> dan menyesuaikan karakter akun <strong>{{ $profile->username }}</strong> guna mengoptimalkan engagement rate.
                </p>

                <div class="videos-table-container">
                    <table class="videos-table" style="min-width: 900px;">
                        <thead>
                            <tr>
                                <th style="width: 100px;">Waktu</th>
                                <th style="width: 120px;">Tema</th>
                                <th>Topik & Ide Konten</th>
                                <th style="width: 250px;">Hook Pembuka (3 dtk)</th>
                                <th style="width: 250px;">Konsep Visual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($profile->aiAnalysis->content_plan as $item)
                                <tr>
                                    <td style="font-weight: 700; color: #fff; font-size: 0.9rem; vertical-align: top;">
                                        <span style="color: var(--accent-indigo);">{{ $item['month'] }}</span><br>
                                        <small style="color: var(--text-secondary); font-weight: 500;">{{ $item['week'] }}</small>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <span class="badge" style="background: rgba(99, 102, 241, 0.15); color: var(--accent-indigo); border: 1px solid rgba(99, 102, 241, 0.3); font-size: 0.7rem;">
                                            {{ $item['theme'] }}
                                        </span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <div style="font-weight: 600; color: #fff; margin-bottom: 0.25rem; font-size: 0.95rem; white-space: normal;">
                                            {{ $item['title'] }}
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--text-secondary); line-height: 1.4; white-space: normal;" title="Caption">
                                            <strong>Caption:</strong> {{ $item['caption'] }}
                                        </div>
                                    </td>
                                    <td style="font-size: 0.85rem; color: var(--text-primary); line-height: 1.5; vertical-align: top; background: rgba(255, 255, 255, 0.01); white-space: normal;">
                                        <i class="fa-solid fa-quote-left" style="font-size: 0.75rem; color: var(--accent-purple); margin-right: 0.25rem;"></i>
                                        <em>{{ $item['hook'] }}</em>
                                    </td>
                                    <td style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.5; vertical-align: top; white-space: normal;">
                                        {{ $item['visual'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Videos Table Section -->
        <div class="glass-card">
            <div class="videos-section-header">
                <h2 style="font-size: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-list-check" style="color: var(--accent-indigo);"></i>
                    Daftar Rincian Video Teranalisis
                </h2>
                <div style="color: var(--text-secondary); font-size: 0.875rem;">
                    Menampilkan {{ $videos->count() }} video terakhir
                </div>
            </div>

            <div class="videos-table-container">
                <table class="videos-table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">No</th>
                            <th>Detail Video & Caption</th>
                            <th>Tanggal Posting</th>
                            <th>Views</th>
                            <th>Likes</th>
                            <th>Komentar</th>
                            <th>Engagement Rate</th>
                            <th style="width: 100px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($videos as $index => $video)
                            <tr>
                                <td style="text-align: center; color: var(--text-secondary); font-weight: 600;">
                                    {{ $index + 1 }}
                                </td>
                                <td>
                                    <div class="video-cell-info">
                                        <img src="{{ $video->thumbnail_url }}" alt="Thumbnail" class="video-thumbnail" onerror="this.src='{{ $profile->avatar_url }}'">
                                        <div>
                                            <div class="video-caption" title="{{ $video->caption }}">{{ $video->caption }}</div>
                                            <span style="font-size: 0.75rem; color: var(--text-secondary); display: block; margin-top: 0.25rem;">ID: {{ $video->video_id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-size: 0.9rem; color: var(--text-secondary);">
                                    {{ $video->post_date->format('d M Y') }}<br>
                                    <small>{{ $video->post_date->format('H:i') }} WIB</small>
                                </td>
                                <td style="font-weight: 600; font-size: 0.95rem; color: var(--text-secondary);">
                                    {{ number_format($video->views) }}
                                </td>
                                <td style="font-weight: 600; font-size: 0.95rem;">
                                    {{ number_format($video->likes) }}
                                </td>
                                <td style="font-weight: 600; font-size: 0.95rem;">
                                    {{ number_format($video->comments) }}
                                </td>
                                <td>
                                    <span class="@if($video->engagement_rate >= 5) engagement-high @elseif($video->engagement_rate >= 2) engagement-medium @else engagement-low @endif" style="font-weight: 700; font-size: 0.95rem;">
                                        {{ number_format($video->engagement_rate, 2) }}%
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <a href="{{ $video->video_url }}" target="_blank" class="btn btn-secondary" style="padding: 0.4rem 0.75rem; font-size: 0.8rem;" title="Lihat Video Asli">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Kunjungi
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Script Section for Rendering Markdown and Charts -->
    <script>
        // Simple client-side Markdown Parser for AI analysis report
        document.addEventListener('DOMContentLoaded', function() {
            const rawMdContainer = document.getElementById('rawMarkdown');
            if (rawMdContainer) {
                const rawMarkdownText = rawMdContainer.innerText;
                const htmlOutput = parseMarkdown(rawMarkdownText);
                document.getElementById('aiMarkdownReport').innerHTML = htmlOutput;
            }

            // Initialize Chart.js
            try {
                if (typeof Chart === 'undefined') {
                    throw new Error("Pustaka Chart.js tidak berhasil dimuat secara lokal.");
                }
                const ctx = document.getElementById('erTrendChart').getContext('2d');
                
                const labels = @json($chartLabels);
                const erData = @json($chartErData);
                const likesData = @json($chartLikesData);

                if (!labels || labels.length === 0) {
                    throw new Error("Tidak ada data video untuk divisualisasikan dalam grafik.");
                }

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Engagement Rate (%)',
                            data: erData,
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.1)',
                            borderWidth: 3,
                            pointBackgroundColor: '#a855f7',
                            pointBorderColor: '#fff',
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            tension: 0.35,
                            fill: true,
                            yAxisID: 'y'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: {
                                    color: '#f3f4f6',
                                    font: {
                                        family: 'Outfit',
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                padding: 12,
                                titleFont: { family: 'Outfit', size: 14, weight: 'bold' },
                                bodyFont: { family: 'Outfit', size: 13 }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.05)'
                                },
                                ticks: {
                                    color: '#9ca3af',
                                    font: { family: 'Outfit' }
                                }
                            },
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.05)'
                                },
                                ticks: {
                                    color: '#9ca3af',
                                    font: { family: 'Outfit' },
                                    callback: function(value) { return value + '%'; }
                                },
                                title: {
                                    display: true,
                                    text: 'Engagement Rate',
                                    color: '#f3f4f6',
                                    font: { family: 'Outfit', weight: 'bold' }
                                }
                            }
                        }
                    }
                });
            } catch (e) {
                console.error("Gagal menginisialisasi Chart.js:", e);
                document.querySelector('.chart-wrapper').innerHTML = `
                    <div style="color: var(--danger); text-align: center; padding: 4rem 1rem; font-weight: 500;">
                        <i class="fa-solid fa-triangle-exclamation" style="font-size: 2.5rem; margin-bottom: 1rem; display: block; color: var(--warning);"></i>
                        <span>Gagal memuat grafik: ${e.message}</span>
                    </div>
                `;
            }
        });

        // Simple and robust parser for heading, bold, bullets, code blocks, dividers, line breaks
        function parseMarkdown(md) {
            let html = md;
            
            // Clean up windows carriage returns
            html = html.replace(/\r\n/g, '\n');

            // Header level 3
            html = html.replace(/^### (.*?)$/gm, '<h3>$1</h3>');
            // Header level 4
            html = html.replace(/^#### (.*?)$/gm, '<h4 style="margin-top: 1rem; margin-bottom: 0.5rem; color: #fff;">$1</h4>');
            // Bold
            html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            
            // List items starting with - or *
            html = html.replace(/^\s*[-*]\s+(.*?)$/gm, '<li>$1</li>');
            
            // Group list items into <ul> tags (runs multiple times to wrap contiguous <li>s)
            html = html.replace(/(<li>.*?<\/li>)+/g, function(match) {
                return '<ul>' + match + '</ul>';
            });
            
            // Fix double nested <ul> elements
            html = html.replace(/<\/ul>\s*<ul>/g, '');

            // Horizontal rules
            html = html.replace(/^---$/gm, '<hr style="border: 0; height: 1px; background: rgba(255,255,255,0.08); margin: 1.5rem 0;">');

            // Paragraphs (split by double lines, wrap non-headers/lists in <p>)
            const lines = html.split('\n');
            let processedLines = [];
            lines.forEach(line => {
                const trimmed = line.trim();
                if (trimmed === '') {
                    return;
                }
                
                // If it is already an HTML block element, do not wrap
                if (trimmed.startsWith('<h') || trimmed.startsWith('<ul') || trimmed.startsWith('<li') || trimmed.startsWith('<hr') || trimmed.startsWith('</ul')) {
                    processedLines.push(line);
                } else {
                    processedLines.push('<p>' + line + '</p>');
                }
            });

            return processedLines.join('\n');
        }
    </script>
</body>
</html>
