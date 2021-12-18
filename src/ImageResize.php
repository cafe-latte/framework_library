<?php
/**
 * Created by PhpStorm.
 * User: sejoong
 * Date: 2017-10-31
 * Time: 오후 2:45
 */

namespace CafeLatte\Libraries;


use CafeLatte\Exception\InvalidLogicException;


/**
 * @author Thorpe Lee <koangbok@gmail.com>
 */
Class ImageResize
{

    /**
     * @var null
     */
    private static $instance = NULL;

    /**
     * @var resource
     */
    private $image;

    /**
     * @var
     */
    private $imageType;

    /**
     * @var int
     */
    private $oldWidth;

    /**
     * @var int
     */
    private $oldHeight;

    /**
     * @var int
     */
    private $newWidth;

    /**
     * @var int
     */
    private $newHeight;

    /**
     * @var
     */
    private $imageResize;

    /**
     * @var
     */
    private $result;

    /**
     * @var
     */
    private $type;

    /**
     * @var
     */
    private $quality;


    /**
     * @param array $originalFile
     * @param string $outputFilePath
     * @param int $newWidth
     * @param int $newHeight
     * @param int $quality
     * @param string $type
     * @return ImageResize
     */
    public static function create(array $originalFile, string $outputFilePath, int $newWidth, int $newHeight, int $quality = 100, string $type = "auto")
    {


        if (self::$instance == NULL) {
            self::$instance = new ImageResize($originalFile, $outputFilePath, $newWidth, $newHeight, $quality, $type);
        }
        return self::$instance;
    }


    /**
     * ImageResize constructor.
     * @param array $originalFile original file name with path
     * @param string $outputFilePath save folder path
     * @param int $newWidth to be resize for width
     * @param int $newHeight to be resize for height
     * @param int $quality image quality ( the value must be between 1 to 100 )
     * @param string $type ( auto / width / height / exact )
     */
    public function __construct(array $originalFile, string $outputFilePath, int $newWidth, int $newHeight, int $quality, string $type)
    {
        $this->InputValidation($newWidth, $newHeight, $quality, $type);
        $this->image = $this->imageValidation($originalFile);
        $this->oldWidth = \imagesx($this->image);
        $this->oldHeight = \imagesy($this->image);
        $this->newWidth = $newWidth;
        $this->newHeight = $newHeight;
        $this->type = $type;
        $this->quality = $quality;
        $this->setResizeImage();
        $this->setCreateImage($outputFilePath);
    }

    /**
     * Check the file is image or Not
     *
     * @param int $newWidth
     * @param int $newHeight
     * @param int $quality
     * @param string $type
     */
    private function InputValidation(int $newWidth, int $newHeight, int $quality, string $type)
    {
        $this->validationWidth($newWidth);
        $this->validationHeight($newHeight);
        $this->validationQuality($quality);
        $this->validationType($type);
    }

    /**
     * @param int $newWidth
     */
    private function validationWidth(int $newWidth)
    {
        if (is_int($newWidth) == false) {
            throw new InvalidLogicException("The width value('{$newWidth}') is Not integer", 400);
        }
    }

    /**
     * @param int $newHeight
     */
    private function validationHeight(int $newHeight)
    {
        if (is_int($newHeight) == false) {
            throw new InvalidLogicException("The height value('{$newHeight}') is NOT integer", 400);
        }
    }

    /**
     * @param int $quality
     */
    private function validationQuality(int $quality)
    {
        if (is_int($quality) == false) {
            throw new InvalidLogicException("The quality value('{$quality}') is Not integer", 400);
        }
        if ($quality < 0 || $quality > 100) {
            throw new InvalidLogicException("The quality value('{$quality}') must be between 1 to 100", 400);
        }
    }

    /**
     * @param string $type
     */
    private function validationType(string $type)
    {
        $types = array("exact", "height", "width", "auto");
        if (in_array($type, $types) == false) {
            throw new InvalidLogicException("The type value('{$type}') is NOT permitted", 400);
        }
    }


    /**
     * @param array $originalFile
     * @return resource
     */
    private function imageValidation(array $originalFile)
    {
        $getImageInfo = getimagesize($originalFile['tmp_name']);
        $this->imageType = $getImageInfo['mime'];
        switch ($this->imageType) {
            case 'image/gif':
                return $img = \imagecreatefromgif($originalFile['tmp_name']);
            case 'image/jpeg':
                return $img = \imagecreatefromjpeg($originalFile['tmp_name']);
            case 'image/png':
                return $img = \imagecreatefrompng($originalFile['tmp_name']);
            default :
                throw new InvalidLogicException("Error : This file is NOT a type of images", 400);
        }
    }

    /**
     *
     */
    private function setResizeImage()
    {
        $reSizeArray = $this->getCriteria($this->type);
        $this->imageResize = \imagecreatetruecolor($reSizeArray['reSetWidth'], $reSizeArray['reSetHeight']);
        \imagecopyresampled($this->imageResize, $this->image, 0, 0, 0, 0, $reSizeArray['reSetWidth'], $reSizeArray['reSetHeight'], $this->oldWidth, $this->oldHeight);
    }


    /**
     * @param string $type
     * @return array
     */
    private function getCriteria(string $type): array
    {
        switch ($type) {
            case 'exact':
                $reSizeArray = $this->getRequestSize($this->newWidth, $this->newHeight);
                break;
            case 'height':
                $reSizeArray = $this->getHeightSize($this->newHeight);
                break;
            case 'width':
                $reSizeArray = $this->getWidthSize($this->newWidth);
                break;
            case 'auto':
                $reSizeArray = $this->getAutoSize($this->newWidth, $this->newHeight);
                break;
            default :
                throw new InvalidLogicException("Error : Undefined Dimensions Type", 400);
        }
        return array('reSetWidth' => $reSizeArray['reSetWidth'], 'reSetHeight' => $reSizeArray['reSetHeight']);
    }

    /**
     * @param int $newWidth
     * @param int $newHeight
     * @return array
     */
    private function getRequestSize(int $newWidth, int $newHeight): array
    {
        return array('reSetWidth' => $newWidth, 'reSetHeight' => $newHeight);
    }

    /**
     * @param int $newHeight
     * @return array
     */
    private function getHeightSize(int $newHeight): array
    {
        $newReWidth = $newHeight * ($this->oldWidth / $this->oldHeight);
        return array('reSetWidth' => $newReWidth, 'reSetHeight' => $newHeight);
    }

    /**
     * @param $newWidth
     * @return array
     */
    private function getWidthSize($newWidth): array
    {
        $newReHeight = $newWidth * ($this->oldHeight / $this->oldWidth);
        return array('reSetWidth' => $newWidth, 'reSetHeight' => $newReHeight);
    }

    /**
     * @param int $newWidth
     * @param int $newHeight
     * @return array
     */
    private function getAutoSize(int $newWidth, int $newHeight): array
    {
        if ($this->oldHeight < $this->oldWidth) {
            $reSizeArray = $this->getWidthSize($newWidth);
        } elseif ($this->oldHeight > $this->oldWidth) {
            $reSizeArray = $this->getHeightSize($newHeight);
        } else {
            if ($newHeight < $newWidth) {
                $reSizeArray = $this->getWidthSize($newWidth);
            } else if ($newHeight > $newWidth) {
                $reSizeArray = $this->getHeightSize($newHeight);
            } else {
                $reSizeArray['reSetWidth'] = $newWidth;
                $reSizeArray['reSetHeight'] = $newHeight;
            }
        }
        return array('reSetWidth' => $reSizeArray['reSetWidth'], 'reSetHeight' => $reSizeArray['reSetHeight']);
    }


    /**
     * @param string $savePath
     */
    public function setCreateImage(string $savePath)
    {
        $name = $this->getNewImageName();
        switch ($this->imageType) {
            case 'image/jpeg':
                $extend = ".jpeg";
                \imagejpeg($this->imageResize, $savePath . $name . $extend, $this->quality);
                break;
            case 'image/gif':
                $extend = ".jpeg";
                \imagegif($this->imageResize, $savePath . $name . $extend);
                break;
            case 'image/png':
                $extend = ".jpeg";
                \imagepng($this->imageResize, $savePath . $name . $extend, $this->quality);
                break;
            default :
                throw new InvalidLogicException("Failure : Creating a new image File Fail", 400);
        }
        \imagedestroy($this->imageResize);
        if (is_file($savePath . $name . $extend) == true) {
            $this->result = $savePath . $name . $extend;
        }
    }

    /**
     * get new file name, it must be unique
     * @return string
     */
    private function getNewImageName(): string
    {
        return \sha1(\uniqid(\getmypid() . \rand(), true));
    }


    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }
}