<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ContentAnalyzer AI - Dashboard</title>
    
    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="spinner"></div>
        <div class="loading-text" id="loadingText">Menganalisis Konten & Memproses AI...</div>
        <div style="color: var(--text-secondary); font-size: 0.875rem;">Mohon tunggu, ini memerlukan waktu beberapa detik</div>
    </div>

    <div class="container">
        <!-- Header -->
        <header>
            <a href="{{ route('dashboard') }}" class="logo">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                ContentAnalyzer AI
            </a>
            <div style="font-size: 0.875rem; color: var(--text-secondary);">
                Status AI: 
                @if(empty(env('GEMINI_API_KEY')))
                    <span style="color: var(--warning);"><i class="fa-solid fa-triangle-exclamation"></i> Simulator Mode</span>
                @else
                    <span style="color: var(--success);"><i class="fa-solid fa-circle-check"></i> Gemini Active</span>
                @endif
            </div>
        </header>

        <!-- Alert Notification -->
        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-xmark"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(empty(env('GEMINI_API_KEY')))
            <div class="alert alert-info" style="margin-bottom: 2rem;">
                <i class="fa-solid fa-circle-info"></i>
                <div>
                    <strong>Catatan Developer:</strong> Kunci API Gemini belum dikonfigurasi di file <code>.env</code>. 
                    Aplikasi saat ini berjalan dalam <strong>Mode Simulasi</strong> dengan performa analisis yang mirip. 
                    Tambahkan <code>GEMINI_API_KEY=kunci_anda</code> di file <code>.env</code> untuk mengaktifkan AI asli.
                </div>
            </div>
        @endif

        <!-- Search Form Card -->
        <div class="glass-card" style="margin-bottom: 3rem;">
            <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-magnifying-glass-chart" style="color: var(--accent-indigo)"></i>
                Mulai Analisis Akun Baru
            </h2>
            <form id="analyzeForm" action="{{ route('analysis.analyze') }}" method="POST">
                @csrf
                <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
                    <div class="form-group">
                        <label for="username">Username Akun</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);">@</span>
                            <input type="text" id="username" name="username" class="input-control" placeholder="username_kreator" value="{{ old('username') }}" required style="padding-left: 28px;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="platform">Platform Media Sosial</label>
                        <select id="platform" name="platform" class="input-control" required>
                            <option value="tiktok" {{ old('platform') == 'tiktok' ? 'selected' : '' }}>TikTok</option>
                            <option value="instagram" {{ old('platform') == 'instagram' ? 'selected' : '' }}>Instagram Reels</option>
                            <option value="youtube" {{ old('platform') == 'youtube' ? 'selected' : '' }}>YouTube Shorts / Videos</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="video_limit">Jumlah Video</label>
                        <select id="video_limit" name="video_limit" class="input-control" required>
                            <option value="20" {{ old('video_limit') == 20 ? 'selected' : '' }}>1 - 20 Video Terakhir</option>
                            <option value="50" {{ old('video_limit') == 50 ? 'selected' : '' }}>1 - 50 Video Terakhir</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="niche">Kategori / Niche Kreator</label>
                        <select id="niche" name="niche" class="input-control" required>
                            <option value="Kuliner (Food)" {{ old('niche') == 'Kuliner (Food)' ? 'selected' : '' }}>Kuliner (Food)</option>
                            <option value="Teknologi & Gadget" {{ old('niche') == 'Teknologi & Gadget' ? 'selected' : '' }}>Teknologi & Gadget</option>
                            <option value="Kecantikan & Fashion" {{ old('niche') == 'Kecantikan & Fashion' ? 'selected' : '' }}>Kecantikan & Fashion</option>
                            <option value="Edukasi & Karir" {{ old('niche') == 'Edukasi & Karir' ? 'selected' : '' }}>Edukasi & Karir</option>
                            <option value="Keuangan & Bisnis" {{ old('niche') == 'Keuangan & Bisnis' ? 'selected' : '' }}>Keuangan & Bisnis</option>
                            <option value="Gaming & Hiburan" {{ old('niche') == 'Gaming & Hiburan' ? 'selected' : '' }}>Gaming & Hiburan</option>
                            <option value="Travel & Gaya Hidup" {{ old('niche') == 'Travel & Gaya Hidup' ? 'selected' : '' }}>Travel & Gaya Hidup</option>
                            <option value="Lifestyle" {{ old('niche') == 'Lifestyle' ? 'selected' : '' }}>Lifestyle / Lainnya</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 1.5rem; margin-bottom: 2rem;">
                    <label for="product_offered">Produk / Jasa yang Ditawarkan (Opsional)</label>
                    <input type="text" id="product_offered" name="product_offered" class="input-control" placeholder="Contoh: Lipstik Matte, Kelas Coding Online, Baju Koko Anak, Jasa Desain Grafis" value="{{ old('product_offered') }}">
                    <small style="color: var(--text-secondary); font-size: 0.8rem; margin-top: 0.25rem;">AI akan mengoptimalkan rencana konten & hook untuk meningkatkan penjualan produk ini secara soft-selling!</small>
                </div>
                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary" style="min-width: 200px;">
                        <i class="fa-solid fa-robot"></i> Analisis Sekarang
                    </button>
                </div>
            </form>
        </div>

        <!-- Analysis History -->
        <div>
            <h2 class="history-title">
                <i class="fa-solid fa-clock-rotate-left" style="color: var(--accent-purple);"></i>
                Riwayat Analisis Akun
            </h2>

            @if($profiles->isEmpty())
                <div class="glass-card" style="text-align: center; padding: 4rem 2rem; border-style: dashed;">
                    <i class="fa-solid fa-folder-open" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                    <p style="color: var(--text-secondary); font-size: 1.1rem; margin-bottom: 1.5rem;">Belum ada riwayat analisis akun.</p>
                    <p style="font-size: 0.9rem; color: var(--text-secondary);">Masukkan username di atas untuk memulai analisis pertama Anda.</p>
                </div>
            @else
                <div class="profile-grid">
                    @foreach($profiles as $profile)
                        <div class="glass-card profile-card">
                            <div>
                                <div class="profile-header">
                                    <img src="{{ $profile->avatar_url }}" alt="{{ $profile->username }}" class="profile-avatar" onerror="this.src='https://ui-avatars.com/api/?name={{ $profile->username }}'">
                                    <div class="profile-meta">
                                        <h3 style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                            {{ $profile->username }}
                                        </h3>
                                        <span>
                                            @if($profile->platform == 'tiktok')
                                                <span class="badge badge-tiktok"><i class="fa-brands fa-tiktok"></i> TikTok</span>
                                            @elseif($profile->platform == 'instagram')
                                                <span class="badge badge-instagram"><i class="fa-brands fa-instagram"></i> Instagram</span>
                                            @elseif($profile->platform == 'youtube')
                                                <span class="badge badge-youtube"><i class="fa-brands fa-youtube"></i> YouTube</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                <div class="profile-stats">
                                    <div class="stat-box">
                                        <div class="stat-label">Followers</div>
                                        <div class="stat-value">
                                            @if($profile->followers >= 1000000)
                                                {{ round($profile->followers / 1000000, 1) }}M
                                            @elseif($profile->followers >= 1000)
                                                {{ round($profile->followers / 1000, 1) }}K
                                            @else
                                                {{ $profile->followers }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="stat-box">
                                        <div class="stat-label">Video Dinalisis</div>
                                        <div class="stat-value">{{ $profile->videos_count }}</div>
                                    </div>
                                </div>

                                <div style="margin-bottom: 1.5rem; display: flex; flex-direction: column; gap: 0.75rem;">
                                    <div>
                                        <div style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 0.25rem;">Niche / Kategori</div>
                                        <div style="font-weight: 600; font-size: 0.95rem; color: #fff; display: flex; align-items: center; gap: 0.4rem;">
                                            <i class="fa-solid fa-tags" style="color: var(--accent-indigo)"></i>
                                            {{ $profile->niche ?? ($profile->aiAnalysis->niche ?? 'Lifestyle') }}
                                        </div>
                                    </div>
                                    @if(!empty($profile->product_offered))
                                        <div>
                                            <div style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 0.25rem;">Produk Ditawarkan</div>
                                            <div style="font-weight: 600; font-size: 0.95rem; color: #fff; display: flex; align-items: center; gap: 0.4rem;">
                                                <i class="fa-solid fa-bag-shopping" style="color: var(--accent-purple)"></i>
                                                <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px; display: inline-block;" title="{{ $profile->product_offered }}">{{ $profile->product_offered }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="profile-footer">
                                <a href="{{ route('analysis.show', $profile->id) }}" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                    <i class="fa-solid fa-chart-simple"></i> Detail
                                </a>
                                
                                <form action="{{ route('analysis.destroy', $profile->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus riwayat analisis untuk {{ $profile->username }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;" title="Hapus Riwayat">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Script to trigger loading state -->
    <script>
        document.getElementById('analyzeForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            if (username !== '') {
                document.getElementById('loadingOverlay').style.display = 'flex';
                // Randomise loading text every 4 seconds to keep it interactive
                const texts = [
                    "Menghubungi Server Sosial Media...",
                    "Mengumpulkan Data Like dan Komentar...",
                    "Menghitung Engagement Rate...",
                    "Menganalisis Konten dengan Model AI Gemini...",
                    "Menyusun Rekomendasi Pertumbuhan Konten..."
                ];
                let textIndex = 0;
                setInterval(() => {
                    if (textIndex < texts.length) {
                        document.getElementById('loadingText').innerText = texts[textIndex];
                        textIndex++;
                    }
                }, 3000);
            }
        });

        // Sembunyikan loading overlay ketika kembali menggunakan tombol back browser
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                document.getElementById('loadingOverlay').style.display = 'none';
            }
        });
    </script>
</body>
</html>
