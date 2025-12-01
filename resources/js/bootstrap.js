import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Placeholder: extend with Echo/Pusher if needed later.
// import Echo from 'laravel-echo';
// window.Echo = new Echo({ ... });

console.debug('[bootstrap] axios initialized');
