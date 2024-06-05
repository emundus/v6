import './App.css';
import "@calcom/atoms/globals.min.css";
import { CalProvider, Booker } from "@calcom/atoms";
function Booking(pageProps: any) {
    const props = pageProps.pageProps;

    return (
        <CalProvider
            clientId={'Je_sers_a_quelque_chose_?'}
            options={{
                apiUrl: "http://localhost:3004/v2",
            }}
        >
            <Booker
                username={props.owner}
                eventSlug={props.slug}
            />
        </CalProvider>
    );
}

export default Booking;
