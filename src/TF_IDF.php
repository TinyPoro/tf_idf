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
    protected $flag = true;

    public function __construct($language, $a = 0.5, $flag=true){
        $this->language = $language;
        $this->stopword_path = $this->resoleStopwordFile($language);

        $this->a = $a;
    }

    protected function resoleStopwordFile($language){
        $file = __DIR__."/stopword/$language.txt";

        if(!file_exists( $file)){
            throw new \Exception("Not support tf.idf for language " . $language);
        }

        return $file;
    }

    public function standardText($text){
        $text = strtolower($text);
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
        $entry = $this->dictionary[$term];

        if($this->flag) $tf = $this->a + (1 - $this->a) * ($entry['postings'][$docId]['tf'] / $this->documents[$docId]['max_tf']);
        else $tf = $entry['postings'][$docId]['tf'];

        $idf = log(count($this->documents) / $entry['df'], 2);

        $tf_idf = $tf * $idf;

        return $tf_idf;
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