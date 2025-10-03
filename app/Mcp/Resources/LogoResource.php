<?php

namespace App\Mcp\Resources;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class LogoResource extends Resource
{
    /**
     * The resource's name.
     */
    protected string $name = 'server-logo';

    /**
     * The resource's title.
     */
    protected string $title = 'Server Logo';

    /**
     * The resource's description.
     */
    protected string $description = 'The official logo for the Laravel MCP Test Server in PNG format.';

    /**
     * The resource's URI.
     */
    protected string $uri = 'assets://images/logo';

    /**
     * The resource's MIME type.
     */
    protected string $mimeType = 'image/png';

    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        // Generate a simple PNG logo programmatically
        $logoData = $this->generateSimpleLogo();
        
        return Response::blob($logoData);
    }

    /**
     * Generate a simple PNG logo programmatically.
     * In a real application, you would typically return file_get_contents() of an actual image file.
     */
    private function generateSimpleLogo(): string
    {
        // Create a simple 200x100 PNG with text
        $width = 200;
        $height = 100;
        
        // Create a blank image
        $image = imagecreate($width, $height);
        
        // Define colors
        $backgroundColor = imagecolorallocate($image, 41, 128, 185); // Blue background
        $textColor = imagecolorallocate($image, 255, 255, 255); // White text
        $borderColor = imagecolorallocate($image, 52, 73, 94); // Dark border
        
        // Fill background
        imagefill($image, 0, 0, $backgroundColor);
        
        // Draw border
        imagerectangle($image, 0, 0, $width - 1, $height - 1, $borderColor);
        imagerectangle($image, 1, 1, $width - 2, $height - 2, $borderColor);
        
        // Add text (if fonts are available)
        $text = 'Laravel MCP';
        $font = 3; // Built-in font
        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);
        $x = ($width - $textWidth) / 2;
        $y = ($height - $textHeight) / 2 - 10;
        
        imagestring($image, $font, $x, $y, $text, $textColor);
        
        $text2 = 'Test Server';
        $textWidth2 = imagefontwidth($font) * strlen($text2);
        $x2 = ($width - $textWidth2) / 2;
        $y2 = $y + $textHeight + 5;
        
        imagestring($image, $font, $x2, $y2, $text2, $textColor);
        
        // Capture the image data
        ob_start();
        imagepng($image);
        $imageData = ob_get_contents();
        ob_end_clean();
        
        // Clean up
        imagedestroy($image);
        
        // If GD is not available or creation failed, return a placeholder
        if (empty($imageData)) {
            // Return a minimal PNG placeholder
            return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        }
        
        return $imageData;
    }
}

