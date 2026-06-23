<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    /**
     * Export video data to a CSV StreamedResponse.
     * Opens perfectly in Excel, Google Sheets, etc.
     *
     * @param string $username
     * @param string $platform
     * @param array|\Illuminate\Support\Collection $videos
     * @return StreamedResponse
     */
    public function exportToCsv(string $username, string $platform, $videos): StreamedResponse
    {
        $fileName = "analisis_{$platform}_{$username}_" . date('Ymd_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['No', 'Video ID', 'Caption/Deskripsi', 'Tanggal Posting', 'Jumlah Views', 'Jumlah Likes', 'Jumlah Komentar', 'Engagement Rate (%)', 'URL Video'];

        $callback = function() use($videos, $columns) {
            $file = fopen('php://output', 'w');
            
            // Prepend UTF-8 BOM to ensure Excel displays Indonesian language, special characters, and emojis properly
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, $columns);

            foreach ($videos as $index => $video) {
                // If it's an object (Eloquent Model)
                if (is_object($video)) {
                    $row = [
                        $index + 1,
                        $video->video_id,
                        $video->caption,
                        $video->post_date->format('Y-m-d H:i:s'),
                        $video->views,
                        $video->likes,
                        $video->comments,
                        $video->engagement_rate,
                        $video->video_url
                    ];
                } else {
                    // If it's an array
                    $row = [
                        $index + 1,
                        $video['video_id'] ?? '',
                        $video['caption'] ?? '',
                        $video['post_date'] ?? '',
                        $video['views'] ?? 0,
                        $video['likes'] ?? 0,
                        $video['comments'] ?? 0,
                        $video['engagement_rate'] ?? 0.0,
                        $video['video_url'] ?? ''
                    ];
                }
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export 3-month content planner to CSV.
     *
     * @param string $username
     * @param string $platform
     * @param array $plannerData
     * @return StreamedResponse
     */
    public function exportPlannerToCsv(string $username, string $platform, array $plannerData): StreamedResponse
    {
        $fileName = "content_planner_3bulan_{$platform}_{$username}_" . date('Ymd_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Bulan', 'Minggu', 'Kategori/Tema', 'Ide Video / Judul', 'Hook (3 Detik Pertama)', 'Konsep Visual & Transisi', 'Draf Caption & Hashtags'];

        $callback = function() use($plannerData, $columns) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, $columns);

            foreach ($plannerData as $item) {
                fputcsv($file, [
                    $item['month'] ?? '',
                    $item['week'] ?? '',
                    $item['theme'] ?? '',
                    $item['title'] ?? '',
                    $item['hook'] ?? '',
                    $item['visual'] ?? '',
                    $item['caption'] ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
