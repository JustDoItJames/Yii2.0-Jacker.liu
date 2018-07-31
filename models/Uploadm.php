<?php
namespace app\models;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use app\components\utils\G;

class Uploadm extends Model
{
    public $imgFile;
    public function rules(){
        return [
            [['imgFile'], 'file','maxFiles' => 20, 'maxSize' => 2048000],//最多20张
        ];
    }
    public function upload()
    {
        if ($this->validate()) {
            foreach ($this->imgFile as $file) {
                $img[] = G::uploadImage($file);
//                $file->saveAs('uploads/' . $file->baseName . '.' . $file->extension);
            }var_dump($img);exit;
            return true;
        } else {
            return false;
        }
    }
}