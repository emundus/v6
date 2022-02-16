import axios from 'axios';

export default (headers = {
    'Content-Type': 'application/x-www-form-urlencoded',
    'Cache-Control': 'no-cache',
}) =>
    axios.create({
        timeout: 30000,
        headers,
        responseType: 'json',
        withCredentials: true,
    });