<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\Video;
use App\Models\AiAnalysis;
use App\Services\ScraperService;
use App\Services\GeminiService;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class DashboardController extends Controller
{
    protected ScraperService $scraper;
    protected GeminiService $gemini;
    protected ExportService $exporter;

    public function __construct(ScraperService $scraper, GeminiService $gemini, ExportService $exporter)
    {
        $this->scraper = $scraper;
        $this->gemini = $gemini;
        $this->exporter = $exporter;
    }

    /**
     * Display the dashboard home with history.
     */
    public function index()
    {
        $profiles = Profile::withCount('videos')
            ->with('aiAnalysis')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('dashboard', compact('profiles'));
    }

    /**
     * Run scraping and AI analysis.
     */
    public function analyze(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:100',
            'platform' => 'required|string|in:tiktok,instagram,youtube',
            'video_limit' => 'required|integer|in:20,50',
            'niche' => 'required|string|max:100',
            'product_offered' => 'nullable|string|max:255',
        ]);

        $username = trim($request->input('username'));
        $platform = $request->input('platform');
        $limit = (int) $request->input('video_limit');

        try {
            DB::beginTransaction();

            // 1. Scrape/Simulate the data
            $scrapedData = $this->scraper->scrape($username, $platform, $limit);

            // 2. Find or create the profile
            $profile = Profile::updateOrCreate(
                [
                    'username' => $scrapedData['username'],
                    'platform' => $scrapedData['platform'],
                ],
                [
                    'followers' => $scrapedData['followers'],
                    'avatar_url' => $scrapedData['avatar_url'],
                    'niche' => $request->input('niche'),
                    'product_offered' => $request->input('product_offered'),
                    'analyzed_at' => now(),
                ]
            );

            // 3. Clear existing videos for this profile to keep it fresh
            $profile->videos()->delete();

            // 4. Save new videos
            foreach ($scrapedData['videos'] as $videoData) {
                $profile->videos()->create($videoData);
            }

            // 5. Run AI Analysis
            $videosForAi = $profile->videos()->get()->toArray();
            $aiResult = $this->gemini->analyze(
                $profile->username, 
                $profile->platform, 
                $videosForAi,
                $profile->niche,
                $profile->product_offered
            );

            // 6. Save or update AI Analysis
            AiAnalysis::updateOrCreate(
                ['profile_id' => $profile->id],
                [
                    'niche' => $aiResult['niche'] ?? $profile->niche ?? 'Unknown',
                    'sentiment' => $aiResult['sentiment'] ?? 'Neutral',
                    'creator_character' => $aiResult['creator_character'] ?? '',
                    'summary' => $aiResult['summary'] ?? '',
                    'recommendations' => $aiResult['recommendations'] ?? [],
                    'raw_analysis' => $aiResult['raw_analysis'] ?? '',
                    'content_plan' => $aiResult['content_plan'] ?? [],
                ]
            );

            DB::commit();

            return redirect()->route('analysis.show', $profile->id)
                ->with('success', 'Analisis berhasil diselesaikan menggunakan AI!');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menganalisis: ' . $e->getMessage());
        }
    }

    /**
     * Show analysis details for a profile.
     */
    public function show(Profile $profile)
    {
        $profile->load(['videos', 'aiAnalysis']);

        $videos = $profile->videos;
        
        if ($videos->isEmpty()) {
            return redirect()->route('dashboard')->with('error', 'Data video tidak ditemukan.');
        }

        // Calculate statistics
        $totalLikes = $videos->sum('likes');
        $totalComments = $videos->sum('comments');
        $totalViews = $videos->sum('views');
        $avgLikes = $videos->avg('likes');
        $avgComments = $videos->avg('comments');
        $avgViews = $videos->avg('views');
        $avgEr = $videos->avg('engagement_rate');

        // Find the top-performing video by engagement rate
        $topVideo = $videos->sortByDesc('engagement_rate')->first();

        // Extract and analyze hashtags from video captions
        $hashtags = [];
        foreach ($videos as $video) {
            if (preg_match_all('/#([a-zA-Z0-9_]+)/u', $video->caption, $matches)) {
                foreach ($matches[0] as $hashtag) {
                    $tagLower = strtolower($hashtag);
                    if (isset($hashtags[$tagLower])) {
                        $hashtags[$tagLower]['count']++;
                        $hashtags[$tagLower]['likes'] += $video->likes;
                        $hashtags[$tagLower]['comments'] += $video->comments;
                    } else {
                        $hashtags[$tagLower] = [
                            'tag' => $hashtag,
                            'count' => 1,
                            'likes' => $video->likes,
                            'comments' => $video->comments,
                        ];
                    }
                }
            }
        }

        // Sort by usage count descending
        uasort($hashtags, function ($a, $b) {
            if ($a['count'] === $b['count']) {
                return $b['likes'] <=> $a['likes'];
            }
            return $b['count'] <=> $a['count'];
        });

        $popularHashtags = array_slice($hashtags, 0, 10);

        // Prepare chart data (Chronological order: oldest to newest)
        $chartVideos = $videos->sortBy('post_date');
        $chartLabels = $chartVideos->map(function ($video) {
            return $video->post_date->format('d M');
        })->toArray();
        $chartErData = $chartVideos->pluck('engagement_rate')->toArray();
        $chartLikesData = $chartVideos->pluck('likes')->toArray();

        return view('analysis-results', compact(
            'profile',
            'videos',
            'totalLikes',
            'totalComments',
            'totalViews',
            'avgLikes',
            'avgComments',
            'avgViews',
            'avgEr',
            'topVideo',
            'popularHashtags',
            'chartLabels',
            'chartErData',
            'chartLikesData'
        ));
    }

    /**
     * Export profile videos to spreadsheet.
     */
    public function export(Profile $profile)
    {
        $videos = $profile->videos;

        if ($videos->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data video untuk diekspor.');
        }

        return $this->exporter->exportToCsv($profile->username, $profile->platform, $videos);
    }

    /**
     * Export 3-month content planner to spreadsheet.
     */
    public function exportPlanner(Profile $profile)
    {
        $profile->load('aiAnalysis');

        if (!$profile->aiAnalysis || empty($profile->aiAnalysis->content_plan)) {
            return redirect()->back()->with('error', 'Rencana konten AI tidak ditemukan untuk diekspor.');
        }

        return $this->exporter->exportPlannerToCsv($profile->username, $profile->platform, $profile->aiAnalysis->content_plan);
    }

    /**
     * Delete profile analysis from history.
     */
    public function destroy(Profile $profile)
    {
        $profile->delete();
        return redirect()->route('dashboard')->with('success', 'Riwayat analisis berhasil dihapus.');
    }
}
