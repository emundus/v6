import axios from 'axios';

axios.defaults.baseURL = '/';

export default (headers = {
    'Content-Type': 'application/json',
    'Cache-Control': 'no-cache',
}) =>
    axios.create({
        timeout: 30000,
        headers,
        responseType: 'json',
        withCredentials: true,
    });
