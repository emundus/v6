import './App.css';
import "@calcom/atoms/globals.min.css";
import { CalProvider, Booker } from "@calcom/atoms";
function Booking(pageProps: any) {
    const props = pageProps.pageProps;

    return (
        <CalProvider
            clientId={'Je_sers_a_quelque_chose_?'}
            options={{
                apiUrl: import.meta.env.URL_API_CALCOM_V2,
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