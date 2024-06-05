import React from 'react';
import './App.css';
import "@calcom/atoms/globals.min.css";
import Availability  from "./Availability.tsx";
import Booking from "./Booking.tsx";


class App extends React.Component<{ pageProps: any }> {
    componentDidMount() {
        console.log('pageProps', this.props.pageProps);
    }
    render() {
        return (
            <main>
                {this.props.pageProps?.mode === '1' ? <Availability pageProps={this.props.pageProps}/> : <Booking pageProps={this.props.pageProps}/>}
            </main>
        );
    }
}

export default App;
