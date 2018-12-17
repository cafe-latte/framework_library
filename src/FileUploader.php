<?php
/*
 * This file is part of Library Framework.
 *
 * (c) Thorpe Lee(Gwangbok Lee) <koangbok@gmail.com>
 *
 * For the full copyright and license information, please view
 * the license that is located at the bottom of this file.
 */

namespace CafeLatte\Libraries;


use CafeLatte\Core\Environment;
use CafeLatte\Exception\FileUploadFailException;

/**
 * @author Thorpe Lee <koangbok@gmail.com>
 */
class FileUploader
{

    /**
     * @var null
     */
    private static $instance = NULL;

    /**
     * upload file
     * @var array type
     */
    private $file;

    /**
     * file type information uploaded
     * @var string type
     */
    private $fileType;

    /**
     * file Temp name(binary) information uploaded
     */
    private $fileTmpName;

    /**
     * file size information uploaded
     */
    private $fileSize;

    /**
     * file error code information uploaded
     */
    private $fileError;

    /**
     * upload folder
     */
    private $uploadPath;
    private $fileName;
    private $prefixFolder;
    private $realFilename;
    private $newFileName;
    private $url;
    private $limitMaxSize;
    private $uploadSubPath;
    private $permitExt;
    private $bannedExt;


    /**
     * @param $subPath
     * @param array $files
     * @return FileUploader|null
     */
    public static function create($subPath, array $files)
    {
        return new FileUploader($subPath, $files);
    }


    /**
     * FileUploader constructor.
     * @param $subPath
     * @param array $files
     */
    public function __construct($subPath, array $files)
    {
        $this->file = $files;
        $this->setUploadPath($subPath);
        $this->setPermitExt();
        $this->setBannedExt();
        $this->setLimitMaxSize();
        $this->setPrefixFolder();
    }


    /**
     * get a file name
     * @return string
     */
    private function getFileName(): string
    {
        return $this->fileName = $this->file['name'];
    }

    /**
     * get a file type
     * @return string
     */
    private function getFileType(): string
    {
        return $this->fileType = $this->file['type'];
    }

    /**
     * get a binary file in tem dir
     * @return string
     */
    private function getFileTempName(): string
    {
        return $this->fileTmpName = $this->file['tmp_name'];
    }

    /**
     * get a size of uploaded file
     * @return string
     */
    private function getFileSize(): string
    {
        return $this->fileSize = $this->file['size'];
    }

    /**
     * get a code of upload's result(
     * @return string
     */
    private function getFileError(): string
    {
        return $this->fileError = $this->file['error'];
    }

    /**
     *
     * @return string
     */
    private function getExtension(): string
    {
        switch ($this->getFileType()) {
            case 'image/jpeg':
                $extend = ".jpeg";
                break;
            case 'image/gif':
                $extend = ".gif";
                break;
            case 'image/png':
                $extend = ".png";
                break;
            default:
                $extend = "." . strtolower(array_pop(explode('.', $this->getFileName())));
                break;
        }

        return $extend;
    }


    /**
     * @param string $subPath
     * @return $this
     */
    public function setUploadPath(string $subPath)
    {
        $this->uploadSubPath = $subPath;

        return $this;
    }

    /**
     * @param array $permitExt
     * @return $this
     */
    public function setPermitExt($permitExt = array("jpg", "jpeg", "gif", "png", "PNG", "GIF", "JPEG", "JPG", "pdf", "docs", "doc", "xlsx", "csv", "pem", "avi", "pem", "pdf", "amr", "zip", "json"))
    {
        $this->permitExt = $permitExt;

        return $this;

    }


    /**
     * @param array $bannedExt
     * @return $this
     */
    public function setBannedExt($bannedExt = array("php", "php4", "php5", "exe", "sh"))
    {
        $this->bannedExt = $bannedExt;

        return $this;
    }


    /**
     * @param string $limitMaxSize
     * @return $this
     */
    public function setLimitMaxSize(string $limitMaxSize = "1048576")
    {
        $this->limitMaxSize = $limitMaxSize;

        return $this;
    }

    /**
     * @param string $uploadSubPath
     * @return $this
     */
    public function setUploadSubPath(string $uploadSubPath = "./")
    {
        $this->uploadSubPath = $uploadSubPath;

        return $this;
    }


    /**
     * @param string $case
     * @return string
     */
    public function setPrefixFolder(string $case = "d")
    {
        switch ($case) {
            case "y":
                $this->prefixFolder = date('Y') . "/";
                break;
            case "m":
                $this->prefixFolder = date('Y-m') . "/";
                break;
            case "d":
                $this->prefixFolder = date('Y-m-d') . "/";
                break;
            case "h":
                $this->prefixFolder = date('Y-m-d') . "/" . date('H') . "/";
                break;
            case "i":
                $this->prefixFolder = date('Y-m-d') . "/" . date('H-i') . "/";
                break;
            default:
                $this->prefixFolder = "";
                break;
        }


        return $this;
    }

    /* --------------------- Validation ------------------------ */

    /**
     *
     */
    private function validationFileErrorCode()
    {
        if ($this->fileError == UPLOAD_ERR_INI_SIZE) {
            throw new FileUploadFailException("Sorry max file size is (" . ini_get('upload_max_filesize') . ")", 400);
        } else if ($this->fileError == UPLOAD_ERR_FORM_SIZE) {
            throw new FileUploadFailException("Sorry max file size over", 400);
        } else if ($this->fileError == UPLOAD_ERR_PARTIAL) {
            throw new FileUploadFailException("a part of file uploaded", 400);
        } else if ($this->fileError == UPLOAD_ERR_NO_FILE) {
            throw new FileUploadFailException("This file can not transport to server", 400);
        }
    }

    /**
     *
     */
    private function validationExtension()
    {
        if (!$this->getExtension()) {
            throw new FileUploadFailException("This file's extension is NOT allowed to upload to server", 400);
        }

    }

    /**
     *
     */
    private function validationSize()
    {
        if ($this->getFileSize() < 0 || $this->getFileSize() > $this->limitMaxSize) {
            throw new FileUploadFailException("Sorry max file size is (" . $this->limitMaxSize . "bite )", 400);
        }
    }

    /**
     *
     */
    private function validationError()
    {
        if ($this->getFileError() != 0) {
            throw new FileUploadFailException("Upload Fail", 400);
        }
    }

    /**
     * This method only validate from the filename.
     */
    private function validationPermitExtension()
    {
        $ext = array_pop(explode(".", $this->getFileName()));
        $chk = false;
        for ($j = 0; $j < count($this->permitExt); $j++) {
            if ($ext == $this->permitExt[$j]) {
                $chk = true;
            }
        }
        for ($i = 0; $i < count($this->bannedExt); $i++) {
            if ($ext == $this->bannedExt[$i]) {
                $chk = false;
            }
        }
        if ($chk == false) {
            throw new FileUploadFailException("This file's extension is NOT allowed to upload to server", 400);
        }
    }

    /* -------------------------------------------------- */

    /**
     * execute to upload to server, if the file type is image(jpg,png,gif). GD library is running for security.
     *
     * @return $this
     */
    public function upload()
    {
        $this->validationFileErrorCode();
        $this->validationExtension();
        $this->validationPermitExtension();
        $this->validationSize();
        $this->validationError();
        $this->setMakeDir();

        $this->newFileName = $this->getNewImageName();

        $saveFileName = Environment::UPLOAD_PATH . $this->uploadSubPath . $this->prefixFolder . $this->newFileName . $this->getExtension();



        $this->url = Environment::PROJECT_URL . $this->uploadSubPath . $this->prefixFolder . $this->newFileName . $this->getExtension();
        $this->realFilename = $this->uploadSubPath . $this->prefixFolder . $this->newFileName . $this->getExtension();

        switch ($this->getFileType()) {
            case "image/jpeg":
                $image = imagecreatefromjpeg($this->getFileTempName());
                imagejpeg($image, $saveFileName, 100);
                break;
            case "image/gif":
                $image = imagecreatefromgif($this->getFileTempName());
                imagejpeg($image, $saveFileName, 100);
                break;
            case "image/png":
                $image = imagecreatefrompng($this->getFileTempName());
                imagejpeg($image, $saveFileName, 100);
                break;
            default :
                if (move_uploaded_file($this->getFileTempName(), $saveFileName) == false) {
                    throw new FileUploadFailException("Fail To Upload", 400);
                }
        }

        if (is_file($saveFileName) == false) {
            throw new FileUploadFailException("Fail To Upload", 400);
        }

        return $this;

    }

    /**
     * @return array
     */
    function getResult(): array
    {
        return array("url" => $this->url, "fileNameAndPath" => $this->realFilename, "fileName" => $this->newFileName . $this->getExtension());

    }

    /**
     * @return string
     */
    function getUploadFileName(): string
    {
        return $this->realFilename;
    }

    /**
     * @return string
     */
    function getUploadFullFileName(): string
    {
        return $this->url;
    }

    /**
     * create a new file name
     *
     * @return string
     */
    private function getNewImageName(): string
    {
        return \sha1(\uniqid(\getmypid() . \rand(), true));
    }


    /**
     * create the new upload folder if not exist
     */
    private function setMakeDir()
    {
        if (is_dir(Environment::UPLOAD_PATH . $this->uploadSubPath . $this->prefixFolder) == false) {
            if (mkdir(Environment::UPLOAD_PATH . $this->uploadSubPath . $this->prefixFolder, 0777) == false) {
                throw new FileUploadFailException("there is no upload directory, Please make this folder ({$this->uploadPath}), Or give the permission (777)", 400);
            }
        }
    }

}
