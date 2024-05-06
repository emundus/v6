//import { useState } from 'react'
import './App.css'
import "@calcom/atoms/globals.min.css";
import Home from "./Home.tsx";
import {Component} from "react";

class App extends Component<{ pageProps: any }> {
    render() {
        let {pageProps} = this.props;
        console.log(pageProps)

        return (
            <main>
                <p>Test</p>
                <Home/>
            </main>
        );
    }
}

export default App
