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

// global variables
let chat = document.getElementById("chat").contentWindow
let display = document.getElementById("display").contentWindow
let base = location.href.replace(/index\.html$/, "")

// inizialize the chat buttons
document.addEventListener("DOMContentLoaded", function() {
    // retrieve index
    fetch(base+"api/my/mastrogpt/index")
    .then( (x)  => x.json())
    .then( (data) => {
        console.log(data)
        let insert = document.getElementById("top-area")
        data.services.forEach(service => {
            const button = document.createElement("button");
            button.textContent = service.name;
            button.onclick = function() {
                let url = base + "api/my/"+service.url
                chat.postMessage({name: service.name, type: "chat", url: url})
            };
            let = p = document.createElement("span")
            p.appendChild(button);
            insert.appendChild(p);
            console.log("enabled "+service.name)
        });
    })
    .catch( (e) => { console.log(e); alert("ERROR: cannot load index") } )
})
