<?php
/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */

//--web true
//--kind php:default
require('vendor/autoload.php');
use Twig\Loader\FilesystemLoader;
use Chess\FenToBoardFactory;
use Chess\Media\BoardToPng;
function main(array $args) : array {
  $out = "";

  $loader = new FilesystemLoader(__DIR__);
  $twig = new \Twig\Environment($loader, []);
  if (array_key_exists("html", $args)) {
    $html = $args['html'];
    $template = $twig->load('html.html');
    $out = $template->render(['html'=>$html]);
  }
  elseif(array_key_exists("code", $args)) {
    $code = $args['code'];
    $language = isset($args['lang']) ? $args['lang'] : '';
    
    $template = $twig->load('editor.html');
    $out = $template->render(['code'=>$code,'language'=>$language]);
  }
  elseif(array_key_exists("chess", $args)) {
    $fen = $args['chess'];
    $temp_dir = sys_get_temp_dir();
    $png_file = null;
    try {
      
      $board = FenToBoardFactory::create($fen);
      $png_file =  (new BoardToPng($board, $flip = true))->output($temp_dir);
      $png_file = sprintf("%s/%s", $temp_dir,$png_file);
      print "Reading $png_file";
      $data = file_get_contents($png_file);

      ob_start(); // Start buffering the output
      $img = imagecreatefromstring($data);
      imagepng($img, null, 0, PNG_NO_FILTER);    
      $b64 = base64_encode(ob_get_contents()); // Get what we've just outputted and base64 it
      imagedestroy($img);
      ob_end_clean();

      $html_image = '<img src="data:image/png;base64,'.$b64.'"/>';
      $template = $twig->load('html.html');
      $out = $template->render(['html'=>$html_image]);
    }
    catch(\Exception $ex) {
      $data =  ["title"=>"Bad Chess Position", "message"=> $ex->getMessage()];
      $template = $twig->load('message.html');
      $out =$template->render($data);
    }
    finally {
      if (file_exists($png_file)) {
        @unlink($png_file);
      } 
    }

  }

  $statusCode = $out=="" ? 204 : 200;
  return [
    "body"=>$out,
    "statusCode"=>$statusCode,
  ];
}