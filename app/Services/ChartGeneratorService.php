<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Process;

class ChartGeneratorService
{
    /**
     * Generate chart images for PDF export
     */
    public function generateChartsForPDF($data): array
    {
        $chartImages = [];
        
        try {
            // Generate assessment status pie chart
            $chartImages['assessment_status'] = $this->generatePieChart(
                'Assessment Completion Status',
                [
                    ['name' => 'Completed', 'value' => $data['studentAnalytics']['assessmentStatus']['completed'] ?? 0, 'color' => '#059669'],
                    ['name' => 'Pending', 'value' => $data['studentAnalytics']['assessmentStatus']['pending'] ?? 0, 'color' => '#d97706']
                ],
                'assessment_status'
            );
        } catch (\Exception $e) {
            $chartImages['assessment_status'] = $this->generateErrorChart('Assessment Status', $e->getMessage());
        }

        try {
            // Generate placement status pie chart
            $rejectedCount = 0;
            if (!empty($data['placementTrends']) && is_array($data['placementTrends'])) {
                $rejectedCount = array_sum(array_column($data['placementTrends'], 'rejected'));
            }
            
            $chartImages['placement_status'] = $this->generatePieChart(
                'Placement Status',
                [
                    ['name' => 'Approved', 'value' => $data['stats']['placedStudents'] ?? 0, 'color' => '#059669'],
                    ['name' => 'Pending', 'value' => $data['stats']['pendingPlacements'] ?? 0, 'color' => '#d97706'],
                    ['name' => 'Rejected', 'value' => $rejectedCount, 'color' => '#dc2626']
                ],
                'placement_status'
            );
        } catch (\Exception $e) {
            $chartImages['placement_status'] = $this->generateErrorChart('Placement Status', $e->getMessage());
        }

        try {
            // Generate category scores bar chart
            $categoryData = $data['studentAnalytics']['categoryScores'] ?? [];
            $chartImages['category_scores'] = $this->generateBarChart(
                'Assessment Scores by Category',
                $categoryData,
                'category_scores'
            );
        } catch (\Exception $e) {
            $chartImages['category_scores'] = $this->generateErrorChart('Category Scores', $e->getMessage());
        }

        try {
            // Generate company placements bar chart
            $companyData = $data['placementAnalytics']['companyPlacements'] ?? [];
            $chartImages['company_placements'] = $this->generateBarChart(
                'Company Placement Performance',
                array_slice($companyData, 0, 8),
                'company_placements'
            );
        } catch (\Exception $e) {
            $chartImages['company_placements'] = $this->generateErrorChart('Company Placements', $e->getMessage());
        }

        try {
            // Generate section performance bar chart
            $sectionData = $data['sectionAnalytics'] ?? [];
            $chartImages['section_performance'] = $this->generateBarChart(
                'Section Performance Comparison',
                $sectionData,
                'section_performance'
            );
        } catch (\Exception $e) {
            $chartImages['section_performance'] = $this->generateErrorChart('Section Performance', $e->getMessage());
        }

        return $chartImages;
    }

    /**
     * Generate a pie chart using a simple HTML/CSS approach
     */
    private function generatePieChart(string $title, array $data, string $filename): string
    {
        $total = array_sum(array_column($data, 'value'));
        $html = $this->getPieChartHTML($title, $data, $total);
        
        // For now, we'll use a simple approach with HTML/CSS
        // In a production environment, you might want to use a more sophisticated chart library
        return $this->saveChartHTML($html, $filename);
    }

    /**
     * Generate a bar chart using HTML/CSS
     */
    private function generateBarChart(string $title, array $data, string $filename): string
    {
        $html = $this->getBarChartHTML($title, $data);
        return $this->saveChartHTML($html, $filename);
    }

    /**
     * Get pie chart HTML
     */
    private function getPieChartHTML(string $title, array $data, float $total): string
    {
        $html = '<div style="text-align: center; font-family: Arial, sans-serif; margin: 20px;">';
        $html .= '<h3 style="margin-bottom: 20px; color: #333;">' . $title . '</h3>';
        
        if ($total > 0) {
            $html .= '<div style="display: flex; justify-content: center; align-items: center; margin: 20px 0;">';
            
            // Create pie chart using CSS
            $html .= '<div style="width: 200px; height: 200px; border-radius: 50%; position: relative; background: conic-gradient(';
            
            $currentAngle = 0;
            $gradientParts = [];
            
            foreach ($data as $item) {
                $percentage = ($item['value'] / $total) * 100;
                $angle = ($percentage / 100) * 360;
                
                $gradientParts[] = $item['color'] . ' ' . $currentAngle . 'deg ' . ($currentAngle + $angle) . 'deg';
                $currentAngle += $angle;
            }
            
            $html .= implode(', ', $gradientParts);
            $html .= ');"></div>';
            
            $html .= '</div>';
            
            // Legend
            $html .= '<div style="display: flex; justify-content: center; flex-wrap: wrap; gap: 15px; margin-top: 20px;">';
            foreach ($data as $item) {
                $percentage = $total > 0 ? round(($item['value'] / $total) * 100, 1) : 0;
                $html .= '<div style="display: flex; align-items: center; gap: 5px;">';
                $html .= '<div style="width: 15px; height: 15px; background-color: ' . $item['color'] . '; border-radius: 3px;"></div>';
                $html .= '<span style="font-size: 12px;">' . $item['name'] . ' (' . $percentage . '%)</span>';
                $html .= '</div>';
            }
            $html .= '</div>';
        } else {
            $html .= '<p style="color: #666;">No data available</p>';
        }
        
        $html .= '</div>';
        return $html;
    }

    /**
     * Get bar chart HTML
     */
    private function getBarChartHTML(string $title, array $data): string
    {
        $html = '<div style="font-family: Arial, sans-serif; margin: 20px;">';
        $html .= '<h3 style="margin-bottom: 20px; color: #333; text-align: center;">' . $title . '</h3>';
        
        if (!empty($data) && is_array($data)) {
            // Filter out items without 'value' key and ensure we have valid data
            $validData = array_filter($data, function($item) {
                return isset($item['value']) && is_numeric($item['value']);
            });
            
            if (!empty($validData)) {
                $values = array_column($validData, 'value');
                $maxValue = max($values);
                
                $html .= '<div style="display: flex; flex-direction: column; gap: 10px; max-width: 600px; margin: 0 auto;">';
                
                foreach ($validData as $item) {
                    $percentage = $maxValue > 0 ? ($item['value'] / $maxValue) * 100 : 0;
                    $html .= '<div style="display: flex; align-items: center; gap: 10px;">';
                    $html .= '<div style="width: 120px; font-size: 12px; text-align: right;">' . htmlspecialchars($item['name'] ?? 'Unknown') . '</div>';
                    $html .= '<div style="flex: 1; height: 25px; background-color: #f0f0f0; border-radius: 3px; position: relative;">';
                    $html .= '<div style="height: 100%; width: ' . $percentage . '%; background-color: #2563eb; border-radius: 3px; transition: width 0.3s;"></div>';
                    $html .= '</div>';
                    $html .= '<div style="width: 60px; font-size: 12px; text-align: left;">' . $item['value'] . '</div>';
                    $html .= '</div>';
                }
                
                $html .= '</div>';
            } else {
                $html .= '<p style="color: #666; text-align: center;">No valid data available</p>';
            }
        } else {
            $html .= '<p style="color: #666; text-align: center;">No data available</p>';
        }
        
        $html .= '</div>';
        return $html;
    }

    /**
     * Save chart HTML to a temporary file
     */
    private function saveChartHTML(string $html, string $filename): string
    {
        $tempPath = storage_path('app/temp/charts/' . $filename . '.html');
        
        // Ensure directory exists
        $directory = dirname($tempPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        file_put_contents($tempPath, $html);
        
        return $tempPath;
    }

    /**
     * Generate an error chart when data is unavailable
     */
    private function generateErrorChart(string $title, string $errorMessage): string
    {
        $html = '<div style="text-align: center; font-family: Arial, sans-serif; margin: 20px; padding: 40px; border: 2px dashed #e5e7eb; border-radius: 8px;">';
        $html .= '<h3 style="margin-bottom: 20px; color: #dc2626;">' . $title . '</h3>';
        $html .= '<div style="color: #6b7280; font-size: 14px;">';
        $html .= '<p>Unable to generate chart</p>';
        $html .= '<p style="font-size: 12px; margin-top: 10px;">' . htmlspecialchars($errorMessage) . '</p>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $this->saveChartHTML($html, 'error_' . strtolower(str_replace(' ', '_', $title)));
    }

    /**
     * Clean up temporary chart files
     */
    public function cleanupTempFiles(array $chartPaths): void
    {
        foreach ($chartPaths as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
        
        // Clean up temp directory if empty
        $tempDir = storage_path('app/temp/charts');
        if (is_dir($tempDir) && count(scandir($tempDir)) <= 2) {
            rmdir($tempDir);
        }
    }
}
