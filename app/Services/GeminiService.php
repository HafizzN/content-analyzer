<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');
    }

    /**
     * Analyze profile content and generate 3-month content planner using Gemini.
     *
     * @param string $username
     * @param string $platform
     * @param array $videos
     * @param string $userNiche
     * @param string|null $productOffered
     * @return array
     */
    public function analyze(string $username, string $platform, array $videos, string $userNiche, ?string $productOffered): array
    {
        $result = $this->performAnalyze($username, $platform, $videos, $userNiche, $productOffered);
        return $this->cleanUtf8($result);
    }

    private function performAnalyze(string $username, string $platform, array $videos, string $userNiche, ?string $productOffered): array
    {
        if (empty($this->apiKey)) {
            return $this->getSimulatedAnalysis($username, $platform, $videos, $userNiche, $productOffered);
        }

        // Format captions and data for the prompt
        $videoDataText = "";
        foreach ($videos as $index => $video) {
            $videoDataText .= "Video #" . ($index + 1) . ":\n";
            $videoDataText .= "- Caption: " . ($video['caption'] ?? 'N/A') . "\n";
            $videoDataText .= "- Likes: " . number_format($video['likes'] ?? 0) . "\n";
            $videoDataText .= "- Comments: " . number_format($video['comments'] ?? 0) . "\n";
            $videoDataText .= "- Views: " . number_format($video['views'] ?? 0) . "\n";
            $videoDataText .= "- Engagement Rate: " . ($video['engagement_rate'] ?? 0) . "%\n\n";
        }

        $productText = empty($productOffered) ? "Tidak ada produk spesifik (personal branding/content creator umum)" : $productOffered;

        $prompt = "Anda adalah pakar Analis Media Sosial, Strategi Konten AI, dan Ahli Pemasaran Digital.
Analisis data performa video berikut untuk kreator:
Username: @{$username}
Platform: {$platform}
Kategori / Niche Utama Kreator: {$userNiche}
Produk / Jasa yang Ditawarkan Kreator untuk Dijual: {$productText}

Berikut adalah data beberapa video terakhir mereka:
{$videoDataText}

PANDUAN UTAMA ANALISIS & STRATEGI:
1. Temukan 3 video dengan performa terbaik (views, likes, atau engagement rate tertinggi - ini adalah konten viral mereka).
2. Analisis mengapa video-video tersebut viral (gaya, tipe hook, visual, atau cara penyampaian caption).
3. Buatlah rekomendasi dan rencana konten 3 bulan (12 minggu) yang MENIRU pola kesuksesan video viral tersebut.
4. PENTING: Pahami bahwa kreator ini adalah seorang **Affiliate** atau **Content Creator yang bekerja sama dengan brand**. Kreator **TIDAK** mempacking paket, tidak memproduksi barang, dan tidak mengirimkan barang sendiri. Jangan merekomendasikan ide konten seperti 'packing barang', 'operasional pengiriman toko', atau 'proses pembuatan di pabrik'.
5. Gantilah ide operasional tersebut dengan ide konten kreator/affiliate yang relevan: unboxing paket PR dari brand, review jujur setelah pemakaian, haul belanja produk, tutorial penggunaan sehari-hari, tips bermanfaat terkait produk tersebut, atau konten transisi visual menggunakan produk tersebut.
6. Gabungkan gaya konten viral tersebut dengan promosi/rekomendasi produk '{$productText}' secara alami sehingga rekomendasinya tidak kaku, dinamis, dan sangat relevan dengan audiens mereka saat ini.

Tolong berikan analisis Anda dalam format JSON dengan struktur persis seperti berikut (jangan sertakan tag markdown ```json di dalam teks balasan Anda, kembalikan hanya string JSON mentah):
{
  \"niche\": \"[Niche Utama dari konten, gunakan kategori: {$userNiche}]\",
  \"sentiment\": \"[Sentimen keseluruhan: Positive / Neutral / Negative]\",
  \"creator_character\": \"[Karakter / Persona Kreator, misal: 'Aesthetic Reviewer / Informative Educator'. Deskripsikan gaya unik pembawaan kontennya, nada bicaranya, dan cara dia berinteraksi dengan audiens dalam 2-3 kalimat]\",
  \"summary\": \"[Rangkuman singkat gaya konten, pembawaan, analisis video viral tersukses, dan keselarasan konten dengan produk/jasa yang ditawarkan: '{$productText}']\",
  \"recommendations\": [
     \"[Rekomendasi 1: Cara menduplikasi gaya hook/visual dari video viral tersukses untuk promosi produk '{$productText}']\",
     \"[Rekomendasi 2: Strategi interaksi berdasarkan pola video teramai]\",
     \"[Rekomendasi 3: Durasi, format, atau sound yang disarankan berdasarkan data performa]\"
  ],
  \"raw_analysis\": \"[Tulis analisis performa mendalam dan terperinci dalam format Markdown lengkap dengan judul, poin-poin, evaluasi video viral paling sukses, kelebihan & kekurangan akun, serta usulan 3 ide konten kreatif spesifik beserta hook-nya yang meniru format konten viral tersebut untuk memicu penjualan produk '{$productText}'. Gunakan bahasa Indonesia yang profesional dan menarik]\",
  \"content_plan\": [
     {
        \"month\": \"Bulan 1\",
        \"week\": \"Minggu 1\",
        \"theme\": \"[Edukasi / Tips / Soft-Selling / Hard-Selling / Behind the Scenes / Q&A / Review]\",
        \"title\": \"[Judul / Topik Ide Video - kaitkan dengan gaya video viral terpopuler]\",
        \"schedule\": \"[Rekomendasi Hari & Jam Posting terbaik untuk niche ini dalam zona waktu WIB (Waktu Indonesia Barat / UTC+7), misal: 'Selasa, 21:00 WIB']\",
        \"hook\": \"[Hook Pembuka 3 Detik Pertama (dialog/teks di layar - adaptasi pola hook viral)]\",
        \"visual\": \"[Konsep Visual & Transisi (misal: Transisi melompat, close up produk)]\",
        \"caption\": \"[Draf Caption & Tagar Terkait, sertakan CTA lembut untuk produk '{$productText}' dan kutip inspirasi video viralnya]\"
     },
     ... (buat tepat 12 item untuk mencakup 12 minggu selama 3 bulan: Bulan 1 Minggu 1-4, Bulan 2 Minggu 1-4, Bulan 3 Minggu 1-4. Sesuaikan tema dengan karakter akun dan promosikan produk '{$productText}' secara halus agar memicu konversi penjualan yang tinggi)
  ]
}";

        // Triple-redundancy fallback model chain
        $models = ['gemini-2.0-flash', 'gemini-1.5-flash', 'gemini-1.5-pro', 'gemini-2.5-flash'];
        
        foreach ($models as $model) {
            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json'
                    ]
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    
                    // Decode JSON
                    $decoded = json_decode($text, true);
                    if (is_array($decoded) && isset($decoded['niche']) && isset($decoded['content_plan'])) {
                        $decoded['is_simulated'] = false;
                        $decoded['api_model_used'] = $model;
                        return $decoded;
                    }
                    
                    Log::warning("Gemini model {$model} returned invalid JSON structure or missing content_plan. Raw response: " . $text);
                } else {
                    Log::error("Gemini model {$model} API call failed: " . $response->body());
                }
            } catch (\Exception $e) {
                Log::error("Gemini model {$model} API Exception: " . $e->getMessage());
            }
        }

        // Fallback to simulated analysis if all API models fail
        return $this->getSimulatedAnalysis($username, $platform, $videos, $userNiche, $productOffered, true);
    }

    /**
     * Generate structured mock analysis when API key is missing or calls fail.
     */
    private function getSimulatedAnalysis(string $username, string $platform, array $videos, string $userNiche = '', ?string $productOffered = '', bool $isFailedApi = false): array
    {
        $niche = !empty($userNiche) ? $userNiche : "Lifestyle";
        $sentiment = "Positive";
        $productText = empty($productOffered) ? "tidak ada produk spesifik (fokus branding)" : $productOffered;

        // Sort videos by views + likes to find viral ones
        usort($videos, function($a, $b) {
            $scoreA = ($a['views'] ?? 0) * 0.7 + ($a['likes'] ?? 0) * 0.3;
            $scoreB = ($b['views'] ?? 0) * 0.7 + ($b['likes'] ?? 0) * 0.3;
            return $scoreB <=> $scoreA;
        });

        $topVideos = array_slice($videos, 0, 3);
        $viralContexts = [];
        foreach ($topVideos as $v) {
            $caption = preg_replace('/#[a-zA-Z0-9_]+/u', '', $v['caption'] ?? '');
            $caption = trim(preg_replace('/\s+/', ' ', $caption));
            if (empty($caption)) {
                $caption = "konten interaktif Anda";
            }
            $shortCap = strlen($caption) > 40 ? substr($caption, 0, 37) . '...' : $caption;
            
            $viralContexts[] = [
                'caption' => $shortCap,
                'views' => number_format($v['views'] ?? 0),
                'likes' => number_format($v['likes'] ?? 0),
                'er' => number_format($v['engagement_rate'] ?? 0, 1) . '%'
            ];
        }

        // Pad if less than 3
        while (count($viralContexts) < 3) {
            $viralContexts[] = [
                'caption' => 'video edukasi branding',
                'views' => '15,000',
                'likes' => '1,200',
                'er' => '4.5%'
            ];
        }

        $viralCaption1 = $viralContexts[0]['caption'];
        $viralViews1 = $viralContexts[0]['views'];
        $viralLikes1 = $viralContexts[0]['likes'];

        $creatorCharacter = "Tipe karakter kreator ini adalah " . ($niche == 'Kecantikan & Fashion' ? 'Aesthetic Reviewer & Style Curator' : 'Informative Educator') . ". Gaya pembawaannya ramah, visualnya rapi dan sinematik, serta berfokus penuh pada pemberian tips penggunaan produk sehari-hari dengan menyertakan tautan afiliasi.";
        
        $summary = "Akun @{$username} di platform " . ucfirst($platform) . " berfokus pada niche {$niche}. Kreator bertindak sebagai Affiliate / Brand Partner untuk mempromosikan: {$productText}. Berdasarkan analisis data, konten viral terbaik Anda ber-caption \"{$viralCaption1}\" dengan {$viralViews1} views. Strategi di bawah dioptimalkan khusus untuk kreator affiliate tanpa melibatkan manajemen pengiriman/produksi fisik.";

        $recommendations = [
            "Duplikasi struktur video viral \"{$viralCaption1}\" dengan mengarahkan audiens ke link affiliate Anda di bio.",
            "Buat konten unboxing paket kiriman brand (PR package) atau haul belanja untuk menampilkan produk '{$productText}' secara visual.",
            "Terapkan teknik transisi estetik atau POV gaya hidup menggunakan produk guna memicu konversi pembelian lewat keranjang kuning/link bio."
        ];

        $rawAnalysis = "### 📊 Analisis Performa Mendalam (@{$username})
*Catatan: Ini adalah analisis simulasi AI karena kendala koneksi API.*

#### 👤 Karakter & Persona Kreator
- **Persona Utama**: **{$creatorCharacter}**
- **Kekuatan Utama**: Interaksi personal yang hangat dan dipercaya oleh pengikut (*trustworthy affiliate*), ditunjang dengan detail ulasan produk yang mendalam.

#### 🔥 Analisis Konten Viral Sukses
- **Postingan Paling Populer**: Video dengan caption *\"{$viralCaption1}\"* berhasil menggaet banyak audiens. Format ini terbukti sangat diminati.
- **Strategi Replikasi Affiliate**: Kami merekomendasikan untuk mempertahankan pembukaan (hook) 3 detik pertama yang serupa, lalu arahkan audiens ke link keranjang belanja di bio.

#### 🔑 Keselarasan Niche & Produk (Sebagai Affiliate)
- **Niche Terpilih**: Kategori **{$niche}** sangat cocok dikombinasikan dengan produk **{$productText}**.
- **Potensi Penjualan**: Penonton memiliki ketertarikan tinggi terhadap review produk. Penjualan affiliate sangat bergantung pada ulasan jujur Anda.

---

### 🚀 3 Ide Konten Kreatif & Penjualan Produk (Berdasarkan Konten Viral Anda)

1. **Ide Konten 1: Unboxing Paket PR dari Brand (Mengikuti \"{$viralCaption1}\")**
   - **Hook Visual**: Teks di layar: *\"Unboxing paket misterius dari brand kosmetik...\"*
   - **Isi Konten**: Membuka kotak pengiriman dari brand, memperlihatkan keindahan isi **{$productText}**, memberikan reaksi jujur.
   - **Format**: Video durasi 15-20 detik dengan background audio tren.

2. **Ide Konten 2: Behind the Scenes Pembuatan Konten**
   - **Hook Visual**: Memperlihatkan setup ringlight: *\"Bongkar rahasia di balik layar video aesthetic saya!\"*
   - **Isi Konten**: Proses Anda merekam video review, meletakkan **{$productText}** di depan cermin, dan proses pengeditan klip.
   - **Format**: Video transisi cepat dengan suara dubbing suara asli Anda.

3. **Ide Konten 3: Q&A Menjawab Keraguan Pembeli (Soft-Selling)**
   - **Hook Visual**: Teks di layar: *\"Membalas @audiens: Kak emang bener produk ini tahan lama?\"*
   - **Isi Konten**: Menjawab keraguan calon pembeli secara informatif sembari mendemokan produk **{$productText}**.
   - **Format**: Berbicara langsung ke kamera dengan pembawaan ramah.";

        $contentPlan = $this->getSimulatedContentPlan($niche, $productText, $videos);

        return [
            'niche' => $niche,
            'sentiment' => $sentiment,
            'creator_character' => $creatorCharacter,
            'summary' => $summary,
            'recommendations' => $recommendations,
            'raw_analysis' => $rawAnalysis,
            'content_plan' => $contentPlan,
            'is_simulated' => true,
            'is_failed_api' => $isFailedApi
        ];
    }

    /**
     * Generate simulated 3-month planner based on niche, product, and viral videos.
     */
    private function getSimulatedContentPlan(string $niche, string $productText, array $videos): array
    {
        $plan = [];
        
        // Sort videos by views + likes to find viral ones
        usort($videos, function($a, $b) {
            $scoreA = ($a['views'] ?? 0) * 0.7 + ($a['likes'] ?? 0) * 0.3;
            $scoreB = ($b['views'] ?? 0) * 0.7 + ($b['likes'] ?? 0) * 0.3;
            return $scoreB <=> $scoreA;
        });

        $topVideos = array_slice($videos, 0, 3);
        $viralContexts = [];
        foreach ($topVideos as $v) {
            $caption = preg_replace('/#[a-zA-Z0-9_]+/u', '', $v['caption'] ?? '');
            $caption = trim(preg_replace('/\s+/', ' ', $caption));
            if (empty($caption)) {
                $caption = "video branding interaktif";
            }
            $shortCap = strlen($caption) > 40 ? substr($caption, 0, 37) . '...' : $caption;
            
            $viralContexts[] = [
                'caption' => $shortCap,
                'views' => number_format($v['views'] ?? 0),
                'likes' => number_format($v['likes'] ?? 0),
                'er' => number_format($v['engagement_rate'] ?? 0, 1) . '%'
            ];
        }

        // Pad if less than 3
        while (count($viralContexts) < 3) {
            $viralContexts[] = [
                'caption' => 'video edukasi branding',
                'views' => '15,000',
                'likes' => '1,200',
                'er' => '4.5%'
            ];
        }

        $nicheTemplates = [
            'Kuliner (Food)' => [
                'Edukasi' => [
                    'title' => 'Resep Rahasia Praktis & Kitchen Hacks',
                    'hook' => 'Teks di layar: "Cara masak cepat tanpa bikin dapur berantakan!"',
                    'visual' => 'Transisi cepat dari bahan-bahan mentah ke masakan jadi yang mengepul lezat.',
                    'caption' => 'Mau masak enak tapi mager cuci piring banyak? Cobain resep praktis ini! Rahasianya ada di [PRODUCT]. Cobain sekarang! [VIRAL_REF] [HASHTAGS]'
                ],
                'Soft-Selling' => [
                    'title' => 'Menu Andalan & Paduan Rasa Gurih Menagih',
                    'hook' => 'Teks di layar: "Review jujur perpaduan rasa ini..."',
                    'visual' => 'Menikmati gigitan makanan dengan ekspresi puas, disusul close-up botol [PRODUCT].',
                    'caption' => 'Perpaduan rasa yang satu ini emang juara banget! Apalagi dipadukan dengan [PRODUCT]. Dapatkan produknya lewat link affiliate di bio! [VIRAL_REF] [HASHTAGS]'
                ],
                'Behind the Scenes' => [
                    'title' => 'Dibalik Layar Studio Masak & Percobaan Resep Baru',
                    'hook' => 'Teks di layar: "Bongkar proses dibalik penataan cahaya agar makanan kelihatan estetik!"',
                    'visual' => 'Melihat letak lampu ringlight, menata piring saji, dan melakukan tes suapan pertama produk [PRODUCT].',
                    'caption' => 'Beginilah perjuangan membuat konten makanan estetik demi mereview produk [PRODUCT] untuk Anda! [VIRAL_REF] [HASHTAGS]'
                ],
                'Review' => [
                    'title' => 'Rekomendasi Rasa & Mengapa Kamu Harus Coba Ini',
                    'hook' => 'Teks di layar: "Ini alasan kenapa produk kuliner ini lagi viral banget!"',
                    'visual' => 'Memamerkan tekstur makanan yang menggiurkan saat disajikan dengan [PRODUCT].',
                    'caption' => 'Banyak yang ketagihan setelah coba pertama kali. Kamu kapan? Yuk klik link bio saya untuk dapatkan [PRODUCT] dengan harga promo! [VIRAL_REF] [HASHTAGS]'
                ]
            ],
            'Teknologi & Gadget' => [
                'Edukasi' => [
                    'title' => 'Tips & Trik Produktivitas & Shortcut Rahasia',
                    'hook' => 'Teks di layar: "Stop kerja lembur! Gunakan pintasan keyboard ini..."',
                    'visual' => 'Tangan mengetik keyboard dengan cepat, disusul screen record workflow yang sangat rapi.',
                    'caption' => 'Trik produktif biar kerjaan cepat selesai dan gak numpuk. Alur kerja makin ringkas berkat bantuan [PRODUCT]! [VIRAL_REF] [HASHTAGS]'
                ],
                'Soft-Selling' => [
                    'title' => 'Rekomendasi Setup Meja & Solusi Kerja Nyaman',
                    'hook' => 'Teks di layar: "Setup minimalis yang bikin fokus 10x lipat!"',
                    'visual' => 'Cinematic shots sudut-sudut meja kerja estetik dengan pencahayaan warm ambient, mendemokan [PRODUCT].',
                    'caption' => 'Meja kerja rapi dan nyaman adalah koentji! Menambahkan [PRODUCT] bikin setup kerja makin produktif. Klik link affiliate saya di bio! [VIRAL_REF] [HASHTAGS]'
                ],
                'Behind the Scenes' => [
                    'title' => 'Dibalik Layar Pembuatan Konten & Memilih Gadget Affiliate Terbaik',
                    'hook' => 'Teks di layar: "Di balik layar pembuatan video review tech minimalis..."',
                    'visual' => 'Membersihkan lensa kamera, mengatur fokus tripod, meletakkan unit [PRODUCT] secara presisi.',
                    'caption' => 'Setiap konten tech dirancang dengan kejujuran agar Anda tidak salah pilih. Dapatkan [PRODUCT] melalui link di bio! [VIRAL_REF] [HASHTAGS]'
                ],
                'Review' => [
                    'title' => 'Review Jujur Kelebihan & Kekurangan Setelah Pemakaian',
                    'hook' => 'Teks di layar: "Review jujur setelah 30 hari pemakaian berat..."',
                    'visual' => 'Menunjukkan detail fisik produk di tangan, memperlihatkan antarmuka software/hardware produk.',
                    'caption' => 'Review jujur performa [PRODUCT]. Apakah layak dibeli di tahun ini? Simak ulasan lengkapnya dan klik link bio! [VIRAL_REF] [HASHTAGS]'
                ]
            ],
            'Kecantikan & Fashion' => [
                'Edukasi' => [
                    'title' => 'Tutorial Tampilan Flawless & Cara Menghindari Crack',
                    'hook' => 'Teks di layar: "Makeup crack saat siang hari? Lakukan tips ini!"',
                    'visual' => 'Wajah bare-face bertransisi menjadi flawless setelah mengaplikasikan base kosmetik.',
                    'caption' => 'Rahasia makeup flawless seharian anti ribet! Jangan lupa pakai [PRODUCT] sebagai rahasia utamamu. [VIRAL_REF] [HASHTAGS]'
                ],
                'Soft-Selling' => [
                    'title' => 'Get Ready With Me (GRWM) & Transisi Outfit Estetik',
                    'hook' => 'Teks di layar: "Yuk siap-siap bareng aku untuk look natural hari ini!"',
                    'visual' => 'Transisi melompat langsung ganti baju, mengaplikasikan lipstik/skincare [PRODUCT] secara kasual.',
                    'caption' => 'Look minimalis tapi tetap terlihat fresh dan elegan. Finishing look-nya menggunakan [PRODUCT] ya! Dapatkan produk ini di link bio saya. [VIRAL_REF] [HASHTAGS]'
                ],
                'Behind the Scenes' => [
                    'title' => 'Dibalik Layar Pembuatan Konten & Unboxing Paket PR Brand',
                    'hook' => 'Teks di layar: "Unboxing Paket PR baru dari brand kecantikan ternama!"',
                    'visual' => 'Membuka box kiriman brand berdesain estetik dengan cutter pink, menunjukkan isi [PRODUCT] baru.',
                    'caption' => 'Senang sekali menerima kiriman baru dari brand! Yuk intip isi paket [PRODUCT] ini dan klik bio untuk membelinya dengan kode diskon! [VIRAL_REF] [HASHTAGS]'
                ],
                'Review' => [
                    'title' => 'Ulasan Sebelum & Setelah Penggunaan Rutin',
                    'hook' => 'Teks di layar: "Hasil nyata pemakaian produk ini selama 14 hari..."',
                    'visual' => 'Menampilkan perbandingan side-by-side kondisi kulit wajah/outfit, diakhiri kemasan mewah [PRODUCT].',
                    'caption' => 'Transformasi nyata kulit jauh lebih lembab dan cerah! Wajib punya nih, klik link di bio saya untuk order [PRODUCT]. [VIRAL_REF] [HASHTAGS]'
                ]
            ],
            'Edukasi & Karir' => [
                'Edukasi' => [
                    'title' => 'Tips Menjawab Interview Kerja & Memperbaiki CV',
                    'hook' => 'Teks di layar: "Jangan jawab \'Saya orangnya bekerja keras\' pas interview!"',
                    'visual' => 'Akting pura-pura menjadi interviewer dan interviewee, menunjuk teks panduan karir di layar.',
                    'caption' => 'Tips interview kerja agar auto dilirik HRD! Tingkatkan kesiapan karirmu melalui program/layanan [PRODUCT]. [VIRAL_REF] [HASHTAGS]'
                ],
                'Soft-Selling' => [
                    'title' => 'Rutinitas Produktif & Cara Meningkatkan Skill',
                    'hook' => 'Teks di layar: "Cara saya menguasai keahlian baru dalam waktu singkat..."',
                    'visual' => 'Membuka laptop, belajar mencatat di notebook, menunjukkan kemajuan sertifikat dari [PRODUCT].',
                    'caption' => 'Investasi terbaik adalah leher ke atas! Yuk upgrade skill kamu dengan [PRODUCT] sekarang. Link pendaftaran kelas ada di bio! [VIRAL_REF] [HASHTAGS]'
                ],
                'Behind the Scenes' => [
                    'title' => 'Di Balik Layar Riset Materi & Persiapan Mengajar Kelas',
                    'hook' => 'Teks di layar: "Mengintip proses riset bahan ajar yang saya siapkan untuk kalian!"',
                    'visual' => 'Membuka buku referensi tebal, mengetik modul di laptop, mencicipi kopi hangat di workspace.',
                    'caption' => 'Semua materi saya ulas dengan teliti agar kalian mudah memahaminya. Jangan lewatkan program [PRODUCT] ya! [VIRAL_REF] [HASHTAGS]'
                ],
                'Review' => [
                    'title' => 'Ulasan Alumni / Testimoni Keberhasilan Belajar',
                    'hook' => 'Teks di layar: "Dari bukan siapa-siapa sampai bisa kerja remote..."',
                    'visual' => 'Membaca komentar positif di WhatsApp/email testimoni, menampilkan antarmuka kelas belajar [PRODUCT].',
                    'caption' => 'Kisah sukses alumni yang berhasil merubah karir mereka! Kamu mau menyusul? Daftar [PRODUCT] sekarang di link bio! [VIRAL_REF] [HASHTAGS]'
                ]
            ],
            'Keuangan & Bisnis' => [
                'Edukasi' => [
                    'title' => 'Cara Mengatur Gaji Bulanan & Investasi Pemula',
                    'hook' => 'Teks di layar: "Gaji 5 juta tapi tabungan nol? Kamu salah kelola uang!"',
                    'visual' => 'Tangan menulis di buku anggaran keuangan, memecah uang tunai ke dalam pos tabungan.',
                    'caption' => 'Kelola keuangan dengan bijak sebelum terlambat! Optimalkan perencanaan bisnismu menggunakan [PRODUCT]. [VIRAL_REF] [HASHTAGS]'
                ],
                'Soft-Selling' => [
                    'title' => 'Peluang Bisnis Modal Minim & Mindset Sukses',
                    'hook' => 'Teks di layar: "Ide bisnis sampingan yang bisa menghasilkan jutaan rupiah..."',
                    'visual' => 'Vlog santai menjelaskan ide bisnis dengan grafik visual sederhana yang dinamis di layar.',
                    'caption' => 'Mulai bisnismu dari hal kecil! Layanan [PRODUCT] siap membantu mengelola pembukuan dan operasional bisnismu. [VIRAL_REF] [HASHTAGS]'
                ],
                'Behind the Scenes' => [
                    'title' => 'Dibalik Layar Rutinitas Riset Konten & Menganalisis Komisi Harian',
                    'hook' => 'Teks di layar: "Gimana saya menganalisis tren bisnis dari meja kerja minimalis ini?"',
                    'visual' => 'Melihat kurva grafik market keuangan, mencatat ide konten di note, membuka laptop memperlihatkan menu [PRODUCT].',
                    'caption' => 'Rutinitas harian riset pasar agar konten tetap akurat. Gunakan [PRODUCT] untuk mempermudah bisnis Anda! [VIRAL_REF] [HASHTAGS]'
                ],
                'Review' => [
                    'title' => 'Bedah Bisnis Viral & Analisis Mengapa Sukses',
                    'hook' => 'Teks di layar: "Kenapa brand X bisa laku keras padahal produknya biasa?"',
                    'visual' => 'Berbicara di depan mic menjelaskan bagan konsep marketing, menunjuk solusi efisiensi dengan [PRODUCT].',
                    'caption' => 'Analisis strategi pemasaran brand terkenal. Terapkan strategi serupa untuk bisnismu menggunakan [PRODUCT]! [VIRAL_REF] [HASHTAGS]'
                ]
            ],
            'Gaming & Hiburan' => [
                'Edukasi' => [
                    'title' => 'Tips & Trik Meningkatkan Aim / Gameplay Juara',
                    'hook' => 'Teks di layar: "Settingan sensitivitas rahasia para pemain pro!"',
                    'visual' => 'Gameplay super cepat dengan transisi beat-sync musik yang keren dan cinematic.',
                    'caption' => 'Tingkatkan performa gameplay kamu! Biar main makin lancar tanpa ngelag, pastikan gunakan [PRODUCT]. [VIRAL_REF] [HASHTAGS]'
                ],
                'Soft-Selling' => [
                    'title' => 'Reaksi Kocak & Gameplay Tantangan Menarik',
                    'hook' => 'Teks di layar: "Tantangan main game tapi matanya ditutup!"',
                    'visual' => 'Video facecam ekspresi terkejut dan tertawa saat bermain game, diakhiri menunjukkan periferal [PRODUCT].',
                    'caption' => 'Momen kocak main game hari ini! Gaming setup kamu belum lengkap kalau belum ada [PRODUCT]. Yuk cek link di bio saya! [VIRAL_REF] [HASHTAGS]'
                ],
                'Behind the Scenes' => [
                    'title' => 'Bongkar Setup PC Gaming & Ruangan Kreatif',
                    'hook' => 'Teks di layar: "Intip setup rahasia tempat saya mengedit video!"',
                    'visual' => 'Kamera bergerak cinematic memutar dari keyboard mekanikal ke monitor gaming dan meletakkan [PRODUCT].',
                    'caption' => 'Tempat ternyaman untuk berkreasi dan push rank. Setup kece ini dimaksimalkan oleh [PRODUCT]! [VIRAL_REF] [HASHTAGS]'
                ],
                'Review' => [
                    'title' => 'Review Jujur Aksesoris Gaming & Rekomendasi Game',
                    'hook' => 'Teks di layar: "Apakah aksesoris seharga ini worth it untuk push rank?"',
                    'visual' => 'Close up detail periferal, membandingkan respon klik, menempelkan stiker produk [PRODUCT].',
                    'caption' => 'Ulasan lengkap performa gaming gear andalan saya. Mau samaan? Dapatkan [PRODUCT] lewat link bio saya sekarang! [VIRAL_REF] [HASHTAGS]'
                ]
            ],
            'Travel & Gaya Hidup' => [
                'Edukasi' => [
                    'title' => 'Rencana Perjalanan Hemat (Itinerary) & Packing Hacks',
                    'hook' => 'Teks di layar: "Itinerary liburan ke Bali 3 hari cuma habis 1 juta!"',
                    'visual' => 'Transisi cepat dari foto peta ke video pemandangan alam pantai berpasir putih.',
                    'caption' => 'Mau liburan hemat tanpa ribet? Ini dia panduan lengkapnya! Jangan lupa bawa [PRODUCT] sebagai teman perjalananmu. [VIRAL_REF] [HASHTAGS]'
                ],
                'Soft-Selling' => [
                    'title' => 'Aesthetic Travel Vlog & Tempat Nongkrong Tersembunyi',
                    'hook' => 'Teks di layar: "Menemukan surga tersembunyi di tengah kota..."',
                    'visual' => 'Cinematic shots suasana kafe estetik dengan kabut pegunungan, menikmati kopi hangat.',
                    'caption' => 'Menikmati ketenangan alam untuk melepas penat kerja. Bawaan wajib hari ini adalah [PRODUCT]. Link ada di bio ya! [VIRAL_REF] [HASHTAGS]'
                ],
                'Behind the Scenes' => [
                    'title' => 'Realita Dibalik Cinematic Vlog & Persiapan Koper',
                    'hook' => 'Teks di layar: "Realita di balik layar pembuatan video aesthetic..."',
                    'visual' => 'Kamera terjatuh, berjalan membawa tripod berat, dilanjutkan menyusun rapi barang bawaan [PRODUCT].',
                    'caption' => 'Di balik keindahan video travel, ada perjuangan angkat tripod! Selalu ditemani oleh [PRODUCT] yang super praktis. [VIRAL_REF] [HASHTAGS]'
                ],
                'Review' => [
                    'title' => 'Review Jujur Villa Murah Pemandangan Indah',
                    'hook' => 'Teks di layar: "Review villa private pool termurah yang pernah saya inapi!"',
                    'visual' => 'Room tour keliling kamar villa aesthetic, memperlihatkan pemandangan kolam renang terbuka.',
                    'caption' => 'Rekomendasi penginapan nyaman untuk liburan akhir pekanmu! Pesan paket liburan lengkap plus [PRODUCT] di bio. [VIRAL_REF] [HASHTAGS]'
                ]
            ],
            'Lifestyle' => [
                'Edukasi' => [
                    'title' => 'Kebiasaan Pagi Orang Sukses & Merapikan Kamar',
                    'hook' => 'Teks di layar: "3 kebiasaan pagi sederhana yang mengubah hidup saya!"',
                    'visual' => 'Membuka gorden kamar tidur, merapikan selimut, dilanjutkan minum air putih estetik.',
                    'caption' => 'Memulai hari dengan energi positif! Rahasia kebiasaan produktif saya selalu ditunjung oleh [PRODUCT]. Yuk cobain! [VIRAL_REF] [HASHTAGS]'
                ],
                'Soft-Selling' => [
                    'title' => 'A Day in My Life & Rutinitas Produktif Harian',
                    'hook' => 'Teks di layar: "A day in my life: Produktif bareng aku yuk!"',
                    'visual' => 'Transisi cepat kegiatan harian (membaca buku, berolahraga, bekerja di laptop) dibarengi [PRODUCT].',
                    'caption' => 'Kunci konsistensi harian adalah disiplin dan alat pendukung yang tepat seperti [PRODUCT]. Info link ada di bio! [VIRAL_REF] [HASHTAGS]'
                ],
                'Behind the Scenes' => [
                    'title' => 'Dibalik Layar Pembuatan Konten Vlog Harian',
                    'hook' => 'Teks di layar: "Bongkar ruang kerja minimalis tempat saya bikin konten!"',
                    'visual' => 'Menata letak ringlight, mengatur posisi duduk, meletakkan produk [PRODUCT] di sudut meja.',
                    'caption' => 'Membuat konten harian butuh persiapan yang matang dan konsistensi. Inilah di balik layarnya bersama [PRODUCT]! [VIRAL_REF] [HASHTAGS]'
                ],
                'Review' => [
                    'title' => 'Unboxing Barang Estetik Penunjang Gaya Hidup',
                    'hook' => 'Teks di layar: "Unboxing barang viral yang bikin hidup lebih aesthetic..."',
                    'visual' => 'Memotong selotip kardus cokelat, mengeluarkan produk bernuansa kayu/putih bersih [PRODUCT].',
                    'caption' => 'Gak nyesel beli barang ini! Kamar jadi makin aesthetic dan hidup makin praktis berkat [PRODUCT]. Klik bio ya! [VIRAL_REF] [HASHTAGS]'
                ]
            ]
        ];

        // Normalize niche key
        $nicheKey = 'Lifestyle';
        foreach (array_keys($nicheTemplates) as $key) {
            // Check substring case-insensitive
            if (stripos($niche, $key) !== false || stripos($key, $niche) !== false) {
                $nicheKey = $key;
                break;
            }
        }

        $nicheData = $nicheTemplates[$nicheKey];
        $themes = array_keys($nicheData); // ['Edukasi', 'Soft-Selling', 'Behind the Scenes', 'Review']

        for ($month = 1; $month <= 3; $month++) {
            $viralIdx = ($month - 1) % 3;
            $viralCap = $viralContexts[$viralIdx]['caption'];
            $viralViews = $viralContexts[$viralIdx]['views'];
            $viralLikes = $viralContexts[$viralIdx]['likes'];
            $viralEr = $viralContexts[$viralIdx]['er'];
            
            $viralRef = "";
            if ($month == 1) {
                $viralRef = "(Terinspirasi dari format video terpopuler Anda: '{$viralCap}' dengan {$viralViews} views)";
            } elseif ($month == 2) {
                $viralRef = "(Mengadopsi gaya penyampaian visual dari video Anda '{$viralCap}' dengan {$viralLikes} likes)";
            } else {
                $viralRef = "(Meniru tingkat interaksi audiens dari video tersukses Anda '{$viralCap}' dengan ER {$viralEr})";
            }

            for ($week = 1; $week <= 4; $week++) {
                $themeName = $themes[($week - 1) % 4];
                $template = $nicheData[$themeName];
                
                $title = $template['title'] . " (Vol. {$month}-{$week})";
                $hook = str_replace('[PRODUCT]', $productText, $template['hook']);
                $visual = str_replace('[PRODUCT]', $productText, $template['visual']);
                
                $captionText = str_replace('[PRODUCT]', $productText, $template['caption']);
                $captionText = str_replace('[VIRAL_REF]', $viralRef, $captionText);
                
                // Add hashtags dynamically
                $hashtags = "";
                if ($nicheKey == 'Kuliner (Food)') {
                    $hashtags = "#Cookingtips #Kulineran #ResepMudah";
                } elseif ($nicheKey == 'Teknologi & Gadget') {
                    $hashtags = "#TechTips #Workspace #RekomendasiGadget";
                } elseif ($nicheKey == 'Kecantikan & Fashion') {
                    $hashtags = "#SkincareRoutine #BaseMakeup #OutfitInspo";
                } elseif ($nicheKey == 'Edukasi & Karir') {
                    $hashtags = "#TipsKarir #BelajarBareng #CodingLife";
                } elseif ($nicheKey == 'Keuangan & Bisnis') {
                    $hashtags = "#TipsBisnis #BelajarSaham #MengaturGaji";
                } elseif ($nicheKey == 'Gaming & Hiburan') {
                    $hashtags = "#GamerLife #FypGaming #MomenKocak";
                } elseif ($nicheKey == 'Travel & Gaya Hidup') {
                    $hashtags = "#TravelVlog #HiddenGem #ItineraryLiburan";
                } else {
                    $hashtags = "#DailyRoutine #VlogAesthetic #InspirasiHidup";
                }
                
                $captionText = str_replace('[HASHTAGS]', $hashtags, $captionText);

                $schedule = "";
                if ($nicheKey == 'Kuliner (Food)' || $nicheKey == 'Travel & Gaya Hidup') {
                    $schedule = match($week) {
                        1 => "Jumat, 19:00 WIB",
                        2 => "Sabtu, 12:00 WIB",
                        3 => "Minggu, 21:00 WIB",
                        default => "Kamis, 19:30 WIB"
                    };
                } else {
                    $schedule = match($week) {
                        1 => "Selasa, 21:00 WIB",
                        2 => "Rabu, 19:30 WIB",
                        3 => "Kamis, 21:00 WIB",
                        default => "Sabtu, 19:00 WIB"
                    };
                }

                $plan[] = [
                    'month' => "Bulan {$month}",
                    'week' => "Minggu {$week}",
                    'theme' => $themeName,
                    'title' => $title,
                    'schedule' => $schedule,
                    'hook' => $hook,
                    'visual' => $visual,
                    'caption' => $captionText,
                ];
            }
        }

        return $plan;
    }

    /**
     * Clean strings recursively to ensure they are valid UTF-8.
     */
    private function cleanUtf8($data)
    {
        if (is_string($data)) {
            return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        } elseif (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->cleanUtf8($value);
            }
        }
        return $data;
    }
}
