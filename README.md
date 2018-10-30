## PHP Framework

These libraries ar working based on Cafelatte Framework is a PHP micro framework that helps you quickly write simple yet powerful web applications and APIs.
These libraries contains uploading, en-decrypting, image-resizing etc. 


## Core Features

* Upload
* Encrypt-Decrypt
* Image Resize

## How To Use

### Upload Files

should contain this code:

    <?php
    
    use CafeLatte\Libraries\FileUploader;
    
    try {
    
      $firstFileName = FileUploader::create("/",$this->request->file('test01'))->upload()->getResult();

      echo $firstFileName01;
    
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }

### En-Decrypt Data

should contain this code:

    <?php
    
    use CafeLatte\Libraries\Endecrypt;
    
    $key = "secureKey_whatever_you_want";
    $iv = "iv";
    $text = "test";
    $cipher = "aes-256-cbc";
    
    $encryptedText = Endecrypt::create($key, $iv, $cipher)->encrypt($text);
    echo "encrypted Text : ". $encryptedText . "\n";
    
    $decryptedText = Endecrypt::create($key, $iv)->decrypt($encryptedText);
    echo "decrypted Text : ".  $decryptedText . "\n";


### Resize Images

should contain this code:

    <?php
    
    use CafeLatte\Libraries\ImageResize;
    
    try {
        $newFileName = ImageResize::create("test.jpg", './', 100, 100, 100, "auto")->getResult();
    
        echo $newFileName;
    
    } catch (Exception $ex) {
        echo $ex->getMessage();
    };


## License

The Library is released under the MIT public license.
