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

// Globals
let invoker = undefined

// Constants
const BOT_IMG = "/img/robot-mini.png";
const PERSON_IMG = "/img/human-mini.png";
const BOT_NAME = "BOT";
const PERSON_NAME = "YOU";

// Page compoents
const msgerForm = document.querySelector(".msger-inputarea");
const msgerInput = document.querySelector(".msger-input");
const msgerChat = document.querySelector(".msger-chat");
const titleChat = document.getElementById("chat-title");
const areaChat = document.getElementById("chat-area");
const displayWindow = window.parent.document.getElementById("display").contentWindow

// Classes
class Invoker {

  constructor(name, url) {
    this.name = name
    this.url = url
    this.state = null
  }

  async invoke(msg) {
    // welcome message no input
    if (msg == null) {
      return "Welcome, you have selected " + this.name;
    }
    // no url 
    if (this.url == null)
      return "Welcome, please select the chat application you want to use by clicking a  button on top.";
    // prepare a request
    let json = {
      input: msg
    }
    if (this.state != null) {
      json['state'] = this.state
    }
    // send the request
    return fetch(this.url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(json)
    })
      .then(r => r.json())
      .then(r => {
        // got answer from the backend
        console.log(r)
        this.state = r.state
        let data = r
        let output = data.output
        delete data['output']
        delete data['state']
        data['type'] = 'chat'
        displayWindow.postMessage(data)
        return output
      })
      .catch(e => {
        console.log(e)
        return `ERROR interacting with ${this.url}`
      })
  }
}

function formatDate(date) {
  const h = "0" + date.getHours();
  const m = "0" + date.getMinutes();
  return `${h.slice(-2)}:${m.slice(-2)}`;
}

function appendMessage(name, img, side, text) {
  //   Simple solution for small apps
  console.log(text)
  let html = marked.parse(text)
  const msgHTML = `
    <div class="msg ${side}-msg">
      <div class="msg-bubble">
        <div class="msg-info">
          <div class="msg-info-name">
            <div class="msg-img" style="background-image: url(${img})"></div>
            <span>${name}</span>
          </div>
          <div class="msg-info-time"> ${formatDate(new Date())}</div>
        </div>
        <div class="msg-text">${html}</div>
      </div>
    </div>
  `;

  msgerChat.insertAdjacentHTML("beforeend", msgHTML);
  msgerChat.scrollTop += 500;
}

function bot(msg) {
  appendMessage(BOT_NAME, BOT_IMG, "left", msg);
}

function human(msg) {
  appendMessage(PERSON_NAME, PERSON_IMG, "right", msg);
  msgerInput.value = "";
}


msgerForm.addEventListener("submit", event => {
  event.preventDefault();

  const input = msgerInput.value;
  if (!input) return;

  human(input);

  if (invoker) {
    invoker.invoke(input).then(reply => bot(reply))
  } else {
    bot("Please select a chat, clicking on one button on the top area.")
  }
});


window.addEventListener('message', async function (ev) {
  if(ev.data.type != "chat")
    return
  console.log(ev);
  invoker = new Invoker(ev.data.name, ev.data.url)
  titleChat.textContent = ev.data.name
  areaChat.innerHTML = ""
  bot(await invoker.invoke(""))
})
