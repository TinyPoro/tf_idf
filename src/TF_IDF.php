<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 7/27/18
 * Time: 1:30 PM
 */

namespace Poro\Tf_Idf;

class TF_IDF{
    public $language;

    protected $dictionary;

    protected $documents;

    protected $stopword_path;

    protected $docId = 1;

    protected $a = 0.5;

    public function __construct($language, $a = 0.5){
        $this->language = $language;
        $this->stopword_path = $this->resoleStopwordFile($language);

        $this->a = $a;

        $this->getResourse();
    }

    private function getResourse(){
        $resource_path = __DIR__."/Resource/$this->language-resource.json";
        if(!file_exists($resource_path)) {
            $resource_file = fopen($resource_path, "w");
            if(!$resource_file) throw new \Exception("Can not open $resource_file!");

            fclose($resource_file);
        }

        $resource = file_get_contents($resource_path);
        if(!$resource) return;

        $data = json_decode($resource, true);
        if(!is_array($data)) return;

        $this->dictionary = $data['dictionary'];
        $this->docId = $data['docId'] + 1;
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

        try{
            $text = $this->removeStopword($text);
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }

        $text = str_replace(['-', '.', ';', ',', '?', ':', '"', '!', '(', ')', '[', ']', '_', '-', '\'', '{', '}', '/'], '', $text);
        $text = preg_replace('/\d+/', '', $text);
        if(!$text) throw new \Exception("Error while standard text!");
        $text = preg_replace('/\s{2,}/', ' ', $text);

        return $text;
    }

    public function removeStopword($text){
        $lines = file($this->stopword_path);
        if(!$lines) throw new \Exception("Can not read ".$this->stopword_path);

        foreach($lines as $line) {
            $text = str_replace($line , ' ', $text);
            if(!$text) throw new \Exception("Error while remove stop words!");
        }

        return $text;
    }

    public function addDocText($text){
        try{
            $text = $this->standardText($text);
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }

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

        $this->writeResource();
    }

    public function writeResource(){
        $data = [
            'docId' => $this->docId,
            'dictionary' => $this->dictionary
        ];

        $value = json_encode($data);
        if(!$value) throw new \Exception("Can not encode data!");

        file_put_contents(__DIR__."/Resource/$this->language-resource.json", $value);
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