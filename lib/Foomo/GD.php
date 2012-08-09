<?php

/*
 * This file is part of the foomo Opensource Framework.
 *
 * The foomo Opensource Framework is free software: you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public License as
 * published  by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * The foomo Opensource Framework is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with
 * the foomo Opensource Framework. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Foomo;

/**
 * a simple gd add on
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 * @internal
 */
class GD
{
	/**
	 * resamples and converts an image from a file to a file
	 * supported mime types are:
	 * 	 'image/gif' (no animaton)
	 * 	 'image/png'
	 * 	 'image/jpeg'
	 *
	 * @param string $srcMime mime type of the source image @see http://php.net/getimagesize
	 * @param string $targetMime mime type of the target
	 * @param string $srcFName file name of the source
	 * @param string $targetFName file name of the target
	 * @param integer $targetW width of the target
	 * @param integer $targetH height of the target
	 * @param integer $targetQuality compression quality of the resulting image
	 * 								0 is a very bad quality at a very small file size
	 * 								100 is a very good quality at a big filesize
	 * 								applies to jpegs only
	 * @return boolean
	 */
	public static function reSampleImage($srcMime, $targetMime, $srcFName, $targetFName, $targetW=null, $targetH=null, $targetQuality = null)
	{
		//sizes
		if (!file_exists($srcFName)) {
			return false;
		}
		$srcData = @getImageSize($srcFName);
		$sourceW = $srcData[0];
		$sourceH = $srcData[1];
		if (!$srcData || $sourceW === 0 || $sourceH === 0) {
			return false;
		}
		$computedSize = self::computeResampledSize($sourceW, $sourceH, $targetW, $targetH);
		$targetW = $computedSize['width'];
		$targetH = $computedSize['height'];
		
		if(is_null($srcMime)) {
			switch($srcData['2']) {
				case IMAGETYPE_GIF:
					$srcMime = 'image/gif';
					break;
				case IMAGETYPE_PNG:
					$srcMime = 'image/png';
					break;
				case IMAGETYPE_JPEG:
					$srcMime = 'image/jpeg';
					break;
			}
		}
		switch ($srcMime) {
			case'image/jpeg':
				$srcImg = ImageCreateFromJpeg($srcFName);
				break;
			case'image/png':
				$srcImg = ImageCreateFromPng($srcFName);
				break;
			case'image/gif':
				// this is a hack to handle gif since animated support is not here yet by defdault
				if (self::detectAnimatedGif($srcFName)) {
					return copy($srcFName, $targetFName);
				} else {
					$srcImg = ImageCreateFromGif($srcFName);
				}
				break;
			default:
				trigger_error('unsopported source mime ' . $srcMime, E_USER_WARNING);
				return false;
		}
		$targetImg = imageCreateTrueColor($targetW, $targetH);
		switch ($targetMime) {
			case'image/png':
				imagesavealpha($targetImg, true);
				$transparentColor = imagecolorallocatealpha($targetImg, 0, 0, 0, 127);
				imagefill($targetImg, 0, 0, $transparentColor);
			case'image/gif':
				imagecopyresampled($targetImg, $srcImg, 0, 0, 0, 0, $targetW, $targetH, $sourceW, $sourceH);
				imagePng($targetImg, $targetFName);
				break;
			case'image/jpeg':
				imagecopyresampled($targetImg, $srcImg, 0, 0, 0, 0, $targetW, $targetH, $sourceW, $sourceH);
				if ($targetQuality != null) {
					imageJpeg($targetImg, $targetFName, $targetQuality);
				} else {
					imageJpeg($targetImg, $targetFName);
				}
				break;
		}
		return true;
	}
	/**
	 * compute a scaled size
	 * 
	 * @param integer $width
	 * @param integer $height
	 * @param integer $targetWidth
	 * @param integer $targetHeight 
	 * 
	 * @return array('width' => int, 'height' => int)
	 */
	private static function computeResampledSize($width, $height, $targetWidth = null, $targetHeight = null)
	{
		if($targetHeight === 0 || $targetWidth === 0) {
			trigger_error('must not resample to size 0', E_USER_ERROR);
		}
		if (isset($targetWidth) && !isset($targetHeight)) {
			$scale = $targetWidth / $width;
			$targetHeight = ceil($scale * $height);
		} else if(!isset($targetWidth) && isset($targetHeight)) {
			$scale = $targetHeight / $height;
			$targetWidth = ceil($scale * $width);
		} else {
			if(is_null($targetWidth)) {
				$targetWidth = $width;
			}
			if(is_null($targetHeight)) {
				$targetHeight = $height;
			}
		}
		return array('width' => $targetWidth, 'height' => $targetHeight);
	}
	/**
	 * resamples an image maintaining it proportion
	 *
	 * @param string $srcMime
	 * @param string $targetMime
	 * @param string $srcFName
	 * @param string $targetFName
	 * @param integer $maxWidth
	 * @param integer $maxHeight
	 * @param integer $targetQuality
	 * @return boolean
	 */
	public static function resampleImageToMaxValues($srcMime, $targetMime, $srcFName, $targetFName, $maxWidth, $maxHeight, $targetQuality = null)
	{
		$size = @getimagesize($srcFName);
		$sourceWidth = $size[0];
		$sourceHeight = $size[1];
		$targetWidth = $maxWidth;
		$targetHeight = $maxHeight;
		$resampledSizeFromWidth = self::computeResampledSize($sourceWidth, $sourceHeight, $maxWidth, null);
		// $resampledSizeFromHeight = self::computeResampledSize($size[0], $size[1], null, $maxheight);
		if($resampledSizeFromWidth['width'] <= $maxWidth && $resampledSizeFromWidth['height'] <= $maxHeight) {
			$targetHeight = null;
		} else {
			$targetWidth = null;
		}
		$gd = new self();
		return $gd->reSampleImage($srcMime, $targetMime, $srcFName, $targetFName, $targetWidth, $targetHeight, $targetQuality);
	}

	/**
	 * Tells you if a GIF image is an animation or not
	 *
	 * @param string $fName file name of the GIF image
	 * @return boolean gif or not
	 */
	public static function detectAnimatedGif($fName)
	{
		$imageInfo = getimagesize($fName);
		if ($imageInfo['mime'] == 'image/gif') {
			$gif = file_get_contents($fName);
			$frameCount = count(preg_split('/\x00[\x00-\xFF]\x00\x2C/', $gif));
			// the frame count is always at least
			if ($frameCount > 2) {
				return true;
			} else {
				return false;
			}
		} else {
			trigger_error($fName . ' is not a gif', E_USER_ERROR);
			return false;
		}
	}

}