<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 7/27/18
 * Time: 1:30 PM
 */

namespace Poro\Tf_Idf;

class TF_IDF{
    protected $dictionary;

    protected $documents;

    protected $docId = 0;

    protected $a = 0.5;
    protected $flag = true;

    public function __construct($a = 0.5, $flag=true){
        $this->a = $a;
    }

    public function addDocText($text){
        $terms = explode(' ', $text);
        $terms = array_values($terms);

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
        return $this->docId;
    }

    protected function addDoc($docId, $text){
        $data = [
            'text' => $text
        ];

        $terms = explode(' ', $text);
        $terms = array_values($terms);

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
        $terms = array_values($terms);

        $tf_idf = 0;

        foreach($terms as $term) {
            $tf_idf += $this->getTfIdf($term, $docId);
        }

        return $tf_idf;
    }
}