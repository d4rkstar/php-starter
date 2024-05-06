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

function main(array $args) : array {
  $language = null;
  $code = null;
  $chess = null;
  $message = null;
  $html = null;

  // initialize state
  $title = "MastroGPT Demo";
  $counter = 1;

  $stateArg = isset($args['state']) ? $args['state'] : 0;
  
  try {
      // get the state if available
      $counter = intval($stateArg) + 1;
  } catch(\Exception $ex) {
      // initialize the state
      $counter = 1;
  }

  $message = sprintf("You made %s requests",$counter);
  $state = strval($counter);

  $input = isset($args['input']) ? $args['input'] : '';
  printf("input='%s'",$input);

  if ($input === "") {
      $output = "Welcome, this is MastroGPT demo chat showing what it can display.\n
      Please select: 'code', 'chess', 'html', 'message'.";
      $message = "Watch here for rich output.";
  } else if ($input === "code") {
      $code =<<<JS
function sum_to(n) {
  let sum = 0;
  for (let i = 1; i <= n; i++) {
      sum += i;
  }
  return sum;
}
JS;
      $language = "javascript";
      $output = sprintf("Here is some JavaScript code.\n```javascript\n%s```",$code);
  } else if ($input === "chess") {
      $chess = "rnbqkbnr/pp1ppppp/8/2p5/4P3/8/PPPP1PPP/RNBQKBNR w KQkq c6 0 2";
      $output = sprintf("Check this chess position.\n\n%s",$chess);
  } else if ($input === "html") {
      $html =<<<HTML
<h1>Sample Form</h1>
<form action="/submit-your-form-endpoint" method="post">
  <div>
      <label for="username">Username:</label>
      <input type="text" id="username" name="username" required>
  </div>
  <div>
      <label for="password">Password:</label>
      <input type="password" id="password" name="password" required>
  </div>
  <div>
      <button type="submit">Login</button>
  </div>
</form>
HTML;
      $output = sprintf("Here is some HTML.\n```html\n%s}\n```",$html);
  } else if ($input === "message") {
      $message = "This is the message.";
      $title = "This is the title";
      $output = "Here is a sample message.";
  } else {
      $output = "No AI here, please type one of 'code', 'chess', 'html', 'message'";
  }

  $res = [
      "output"=>$output,
  ];

  if ($language) $res['language'] = $language;
  if ($message) $res['message'] = $message;
  if ($state) $res['state'] = $state;
  if ($title) $res['title'] = $title;
  if ($chess) $res['chess'] = $chess;
  if ($code) $res['code'] = $code;
  if ($html) $res['html'] = $html;

  return [ "body"=>$res ];
}