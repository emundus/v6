import React from 'react'
import ReactDOM from 'react-dom/client'
import App from './App.tsx'
import './index.css'

let attributes: any = {};
let rootBlock = document.getElementById('root');
if (rootBlock) {
    attributes = rootBlock.getAttribute('data-attributes');
    if(attributes) {
        attributes = JSON.parse(attributes);
    }
}

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <App pageProps={attributes} />
  </React.StrictMode>,
)
