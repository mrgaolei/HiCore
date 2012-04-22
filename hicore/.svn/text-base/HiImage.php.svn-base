<?php

class HiImage {

    var $w_pct = 100;
    var $w_quality = 80;
    var $w_minwidth = 300;
    var $w_minheight = 300;
    var $thumb_enable = true;
    var $watermark_enable = true;
    var $interlace = 0;

    function __construct() {
        $this->set();
    }

    function HiImage() {
        $this->__construct();
    }

    function set($w_minwidth = 300, $w_minheight = 300, $w_quality = 80, $w_pct = 100) {
        $this->w_minwidth = $w_minwidth;
        $this->w_minheight = $w_minheight;
        $this->w_quality = $w_quality;
        $this->w_pct = $w_pct;
    }

    function info($img) {
        $imageinfo = getimagesize($img);
        if ($imageinfo === false)
            return false;
        $imagetype = strtolower(substr(image_type_to_extension($imageinfo[2]), 1));
        $imagesize = filesize($img);
        $info = array(
            'width' => $imageinfo[0],
            'height' => $imageinfo[1],
            'type' => $imagetype,
            'size' => $imagesize,
            'mime' => $imageinfo['mime']
        );
        return $info;
    }

	function thumb3($image, $filename = '', $maxwidth = 200, $maxheight = 50, $suffix='_thumb', $autocut = 0, $ftp = 0) {
        if (!$this->thumb_enable || !$this->check($image))
            return false;
        $info = self::info($image);
        if ($info === false)
            return false;
        $srcwidth = $info['width'];
        $srcheight = $info['height'];
        $pathinfo = pathinfo($image);
        $type = $pathinfo['extension'];
        //if (!$type)
            $type = $info['type'];
        $type = strtolower($type);
        # 如果gif图尺寸不大于最大尺寸，则直接复制，不做任何处理
        if ($type == 'gif' && $srcwidth <= $maxwidth && ($maxheight == 0 || $srcheight <= $maxheight)) {
        	copy($image, $filename);
        	return $filename;
        }
        unset($info);

        $scale = min($maxwidth / $srcwidth, $maxheight / $srcheight);
        # $createwidth = $width = (int) ($srcwidth * $scale);
        $createwidth = $width = $maxwidth;
        # $createheight = $height = (int) ($srcheight * $scale);
        $createheight = $height = (int) ($srcheight * ($maxwidth / $srcwidth));
        if ($maxwidth >= $srcwidth) {
            $createwidth = $width = $srcwidth;
            $createheight = $height = $srcheight;
        }
        $psrc_x = $psrc_y = 0;
        $createfun = 'imagecreatefrom' . ($type == 'jpg' ? 'jpeg' : $type);
        $srcimg = $createfun($image);
        if ($type != 'gif' && function_exists('imagecreatetruecolor'))
            $thumbimg = imagecreatetruecolor($createwidth, $createheight);
        else
            $thumbimg = imagecreate($width, $height);
        imagefill($thumbimg, 0, 0, 0xffffff);
        if (function_exists('imagecopyresampled'))
            imagecopyresampled($thumbimg, $srcimg, 0, 0, $psrc_x, $psrc_y, $width, $height, $srcwidth, $srcheight);
        else
            imagecopyresized($thumbimg, $srcimg, 0, 0, $psrc_x, $psrc_y, $width, $height, $srcwidth, $srcheight);
        if ($type == 'gif' || $type == 'png') {
            $background_color = imagecolorallocate($thumbimg, 0, 255, 0);  //  ָ��һ����ɫ
            imagecolortransparent($thumbimg, $background_color);  //  ����Ϊ͸��ɫ����ע�͵������������ɫ��ͼ
        }
        if ($type == 'jpg' || $type == 'jpeg')
            imageinterlace($thumbimg, $this->interlace);
        $imagefun = 'image' . ($type == 'jpg' ? 'jpeg' : $type);
        if (empty($filename))
            $filename = substr($image, 0, strrpos($image, '.')) . $suffix . '.' . $type;
		if ($imagefun == 'imagejpeg') {
        	$imagefun($thumbimg, $filename, 92);
        } else {
        	$imagefun($thumbimg, $filename);
        }
        imagedestroy($thumbimg);
        imagedestroy($srcimg);
        if ($ftp) {
            @unlink($image);
        }
        return $filename;
    }
    
	function thumb($image, $filename = '', $maxwidth = 200, $maxheight = 50, $suffix='_thumb', $autocut = 0, $ftp = 0) {
        if (!$this->thumb_enable || !$this->check($image))
            return false;
        $info = self::info($image);
        if ($info === false)
            return false;
        $srcwidth = $info['width'];
        $srcheight = $info['height'];
        $pathinfo = pathinfo($image);
        $type = $pathinfo['extension'];
        //if (!$type)
            $type = $info['type'];
        $type = strtolower($type);
    	# 如果gif图尺寸不大于最大尺寸，则直接复制，不做任何处理
        if ($type == 'gif' && $srcwidth <= $maxwidth && $srcheight <= $maxheight) {
        	copy($image, $filename);
        	return $filename;
        }

        unset($info);

        $scale = min($maxwidth / $srcwidth, $maxheight / $srcheight);
        $createwidth = $width = (int) ($srcwidth * $scale);
        $createheight = $height = (int) ($srcheight * $scale);
        if ($maxwidth >= $srcwidth && $maxheight >= $srcheight) {
            $createwidth = $width = $srcwidth;
            $createheight = $height = $srcheight;
        }
        $psrc_x = $psrc_y = 0;
        if ($autocut) {
            if ($maxwidth / $maxheight < $srcwidth / $srcheight && $maxheight >= $height) {
                $width = $maxheight / $height * $width;
                $height = $maxheight;
            } elseif ($maxwidth / $maxheight > $srcwidth / $srcheight && $maxwidth >= $width) {
                $height = $maxwidth / $width * $height;
                $width = $maxwidth;
            }
            $createwidth = $maxwidth;
            $createheight = $maxheight;
        }
        $createfun = 'imagecreatefrom' . ($type == 'jpg' ? 'jpeg' : $type);
        $srcimg = $createfun($image);
        if ($type != 'gif' && function_exists('imagecreatetruecolor'))
            $thumbimg = imagecreatetruecolor($createwidth, $createheight);
        else
            $thumbimg = imagecreate($width, $height);
		imagefill($thumbimg, 0, 0, 0xffffff);
        if (function_exists('imagecopyresampled'))
            imagecopyresampled($thumbimg, $srcimg, 0, 0, $psrc_x, $psrc_y, $width, $height, $srcwidth, $srcheight);
        else
            imagecopyresized($thumbimg, $srcimg, 0, 0, $psrc_x, $psrc_y, $width, $height, $srcwidth, $srcheight);
        if ($type == 'gif' || $type == 'png') {
            $background_color = imagecolorallocate($thumbimg, 0, 255, 0);  //  ָ��һ����ɫ
            imagecolortransparent($thumbimg, $background_color);  //  ����Ϊ͸��ɫ����ע�͵������������ɫ��ͼ
        }
        if ($type == 'jpg' || $type == 'jpeg')
            imageinterlace($thumbimg, $this->interlace);
        $imagefun = 'image' . ($type == 'jpg' ? 'jpeg' : $type);
        if (empty($filename))
            $filename = substr($image, 0, strrpos($image, '.')) . $suffix . '.' . $type;
        if ($imagefun == 'imagejpeg') {
        	$imagefun($thumbimg, $filename, 92);
        } else {
        	$imagefun($thumbimg, $filename);
        }
        imagedestroy($thumbimg);
        imagedestroy($srcimg);
        if ($ftp) {
            @unlink($image);
        }
        return $filename;
    }

    function watermark($source, $target = '', $w_pos = 0, $w_img = '', $w_text = '', $w_font = 5, $w_color = '#ff0000') {
        if (!$this->watermark_enable || !$this->check($source))
            return false;
        if (!$target)
            $target = $source;
        $source_info = getimagesize($source);
        $source_w = $source_info[0];
        $source_h = $source_info[1];
        if ($source_w < $this->w_minwidth || $source_h < $this->w_minheight)
            return false;
        switch ($source_info[2]) {
            case 1 :
                $source_img = imagecreatefromgif($source);
                break;
            case 2 :
                $source_img = imagecreatefromjpeg($source);
                break;
            case 3 :
                $source_img = imagecreatefrompng($source);
                break;
            default :
                return false;
        }
        if (!empty($w_img) && file_exists($w_img)) {
            $ifwaterimage = 1;
            $water_info = getimagesize($w_img);
            $width = $water_info[0];
            $height = $water_info[1];
            switch ($water_info[2]) {
                case 1 :
                    $water_img = imagecreatefromgif($w_img);
                    break;
                case 2 :
                    $water_img = imagecreatefromjpeg($w_img);
                    break;
                case 3 :
                    $water_img = imagecreatefrompng($w_img);
                    break;
                default :
                    return;
            }
        } else {
            $ifwaterimage = 0;
            //exit(APP_ROOT . '/font.ttf');
            $temp = imagettfbbox(ceil($w_font * 2.5), 0, APP_ROOT . '/font.ttf', $w_text); //ȡ��ʹ�� truetype ������ı��ķ�Χ
            $width = $temp[2] - $temp[6];
            $height = $temp[3] - $temp[7];
            unset($temp);
        }
        switch ($w_pos) {
            case 0:
                $wx = rand(0, ($source_w - $width));
                $wy = rand(0, ($source_h - $height));
                break;
            case 1:
                $wx = 5;
                $wy = 5;
                break;
            case 2:
                $wx = ($source_w - $width) / 2;
                $wy = 0;
                break;
            case 3:
                $wx = $source_w - $width;
                $wy = 0;
                break;
            case 4:
                $wx = 0;
                $wy = ($source_h - $height) / 2;
                break;
            case 5:
                $wx = ($source_w - $width) / 2;
                $wy = ($source_h - $height) / 2;
                break;
            case 6:
                $wx = $source_w - $width;
                $wy = ($source_h - $height) / 2;
                break;
            case 7:
                $wx = 0;
                $wy = $source_h - $height;
                break;
            case 8:
                $wx = ($source_w - $width) / 2;
                $wy = $source_h - $height;
                break;
            case 9:
                $wx = $source_w - $width;
                $wy = $source_h - $height;
                break;
            default:
                $wx = rand(0, ($source_w - $width));
                $wy = rand(0, ($source_h - $height));
                break;
        }
        if ($ifwaterimage) {
            if ($water_info[2] == 3) {
                imageCopy($source_img, $water_img, $wx, $wy, 0, 0, $width, $height);
            } else {
                imagecopymerge($source_img, $water_img, $wx, $wy, 0, 0, $width, $height, $this->w_pct);
            }
        } else {
            if (!empty($w_color) && (strlen($w_color) == 7)) {
                $r = hexdec(substr($w_color, 1, 2));
                $g = hexdec(substr($w_color, 3, 2));
                $b = hexdec(substr($w_color, 5));
            } else {
                return;
            }
            //imagestring($source_img, $w_font, $wx, $wy, $w_text, imagecolorallocate($source_img, $r, $g, $b));
            imagettftext($source_img, ceil($w_font * 2.5), 0, $wx+1, $wy+1 + ceil($w_font * 2.5), imagecolorallocate($source_img, 255, 255, 255), APP_ROOT . '/font.ttf', $w_text);
            imagettftext($source_img, ceil($w_font * 2.5), 0, $wx, $wy + ceil($w_font * 2.5), imagecolorallocate($source_img, $r, $g, $b), APP_ROOT . '/font.ttf', $w_text);
        }
        switch ($source_info[2]) {
            case 1 :
                imagegif($source_img, $target);
                break;
            case 2 :
                imagejpeg($source_img, $target, $this->w_quality);
                break;
            case 3 :
                imagepng($source_img, $target);
                break;
            default :
                return;
        }
        if (isset($water_info)) {
            unset($water_info);
        }
        if (isset($water_img)) {
            imagedestroy($water_img);
        }
        unset($source_info);
        imagedestroy($source_img);
        return true;
    }

    function check($image) {
        return extension_loaded('gd') && preg_match("/\.(jpg|jpeg|gif|png)/i", $image, $m) && file_exists($image) && function_exists('imagecreatefrom' . ($m[1] == 'jpg' ? 'jpeg' : $m[1]));
    }

}
