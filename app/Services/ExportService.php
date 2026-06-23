<?php

namespace App\Services;

use Illuminate\Http\Response;

class ExportService
{
    /**
     * Export video data to a CSV Response.
     * Opens perfectly in Excel, Google Sheets, etc.
     *
     * @param string $username
     * @param string $platform
     * @param array|\Illuminate\Support\Collection $videos
     * @return Response
     */
    public function exportToCsv(string $username, string $platform, $videos): Response
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

        $output = fopen('php://temp', 'r+');
        
        // Prepend UTF-8 BOM to ensure Excel displays Indonesian language, special characters, and emojis properly
        fwrite($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, $columns);

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
            fputcsv($output, $row);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return response($csvContent, 200, $headers);
    }

    /**
     * Export 3-month content planner to CSV.
     *
     * @param string $username
     * @param string $platform
     * @param array $plannerData
     * @return Response
     */
    public function exportPlannerToCsv(string $username, string $platform, array $plannerData): Response
    {
        $fileName = "content_planner_3bulan_{$platform}_{$username}_" . date('Ymd_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Bulan', 'Minggu', 'Kategori/Tema', 'Ide Video / Judul', 'Jadwal Posting (Hari & Jam)', 'Hook (3 Detik Pertama)', 'Konsep Visual & Transisi', 'Draf Caption & Hashtags'];

        $output = fopen('php://temp', 'r+');
        
        // UTF-8 BOM
        fwrite($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, $columns);

        foreach ($plannerData as $item) {
            fputcsv($output, [
                $item['month'] ?? '',
                $item['week'] ?? '',
                $item['theme'] ?? '',
                $item['title'] ?? '',
                $item['schedule'] ?? $item['posting_schedule'] ?? '',
                $item['hook'] ?? '',
                $item['visual'] ?? '',
                $item['caption'] ?? ''
            ]);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return response($csvContent, 200, $headers);
    }
}
