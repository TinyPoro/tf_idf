<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 7/27/18
 * Time: 1:30 PM
 */

namespace Poro\Tf_Idf;

ini_set('memory_limit', '2048M');

class TF_IDF{
    public $language;

    protected $dictionary;

    protected $documents;

    protected $stopword_path;

    public $docId = 1;

    protected $a = 0.5;

    public function __construct($language, $a = 0.5){
        $this->language = $language;
        try{
            $this->stopword_path = $this->resoleStopwordFile($language);
        }catch (\Exception $e){
            throw new \Exception('Can not resolve stop words!');
        }

        $this->a = $a;
    }

    public static function getObject($language, $a = 0.5){
        try{
            $obj = self::getResourse($language);
            $obj->stopword_path = $obj->resoleStopwordFile($language);

            return $obj;
        }catch (\Exception $e){
            return new TF_IDF($language, $a);
        }
    }

    private static function getResourse($language){
        $resource_path = __DIR__."/Resource/$language-resource";
        if(!file_exists($resource_path)) {
            $resource_file = fopen($resource_path, "w");
            fclose($resource_file);
        }

        try{
            $binary_array = [];

            $lines = file($resource_path);
            if(!$lines) throw new \Exception('Resource path empty!');

            foreach ($lines as $line) {
                $datas = explode(' ', $line);
                foreach ($datas as $data){
                    $binary_array[] = $data;
                }
            }

            $serialized_data = call_user_func_array('pack', array_merge(array('C*'), $binary_array));
            $obj = unserialize($serialized_data);

            return $obj;
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    public static function writeResource(TF_IDF $obj){
        try{
            $resource_path = __DIR__."/Resource/$obj->language-resource";

            //convert obj to binary data
            $serialized_data = serialize($obj);
            $binary_data = unpack("C*",$serialized_data);

            //store binary data in binary file
            $file = fopen($resource_path, 'w+');
            $bin_str = '';
            foreach ($binary_data as $data) {
                $bin_str .= $data." ";
            }
            fwrite($file, $bin_str);
            fclose($file);
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    protected function resoleStopwordFile($language){
        $file = __DIR__."/stopword/$language.txt";

        if(!file_exists( $file)){
            throw new \Exception("Not support tf.idf for language " . $language);
        }

        return $file;
    }

    public function standardText($text){
        $text = mb_strtolower($text);
        $text = trim($text);

        $text = $this->removeStopword($text);

        $text = str_replace(['-', '.', ';', ',', '?', ':', '"', '!', '(', ')', '[', ']', '_', '-', '\'', '{', '}', '/'], '', $text);
        $text = preg_replace('/\d+/', '', $text);
        $text = preg_replace('/\s{2,}/', ' ', $text);

        return $text;
    }

    public function removeStopword($text){
        $lines = file($this->stopword_path);

        foreach($lines as $line) {
            $text = str_replace($line , ' ', $text);
        }

        return $text;
    }

    public function addDocText($text){
        $text = $this->standardText($text);

        $terms = explode(' ', $text);
        $terms = array_filter($terms);

        foreach($terms as $term) {
            if(!isset($this->dictionary[$term])) {
                $this->dictionary[$term] = array('df' => 0, 'postings' => array());
            }
            if(!isset($dictionary[$term]['postings'][$this->docId])) {
                $this->dictionary[$term]['df']++;
                $this->dictionary[$term]['postings'][$this->docId] = array('tf' => 0);
            }

            $this->dictionary[$term]['postings'][$this->docId]['tf']++;
        }

        $this->addDoc($this->docId, $text);

        $this->docId++;
        return $this->docId - 1;
    }

    protected function addDoc($docId, $text){
        $data = [
            'text' => $text
        ];

        $terms = explode(' ', $text);
        $terms = array_filter($terms);

        $max_tf = 0;

        foreach($terms as $term) {
            $tf = $this->dictionary[$term]['postings'][$docId]['tf'];
            if($tf > $max_tf) $max_tf = $tf;
        }

        $data['max_tf'] = $max_tf;

        $this->documents[$docId] = $data;
    }

    public function getTfIdf($term, $docId){
        try{
            $entry = $this->dictionary[$term];

            $tf = $this->a + (1 - $this->a) * ($entry['postings'][$docId]['tf'] / $this->documents[$docId]['max_tf']);

            $idf = log(count($this->documents) / $entry['df'], 2);

            $tf_idf = $tf * $idf;

            return -$tf_idf;
        }catch (\Exception $e){
            return 0;
        }


    }

    public function getDocTfIdf($docId){
        $text = $this->documents[$docId]['text'];
        if(!$text) throw new \Exception("Document $docId does not exist!");

        $terms = explode(' ', $text);
        $terms = array_filter($terms);

        $tf_idf = 0;

        foreach($terms as $term) {
            $tf_idf += $this->getTfIdf($term, $docId);
        }

        return $tf_idf;
    }
}