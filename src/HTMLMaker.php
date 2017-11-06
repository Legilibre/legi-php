<?php

class HTMLMaker {

  public static function makeSelect($values) {
      $html_content = '';
      foreach($values as $value) {
        $html_content .=  '<option value="'. $value .'">' .$value. '</option>';
      }
    return $html_content;
  }
}