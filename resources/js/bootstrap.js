import ajax from '@imacrayon/alpine-ajax'
import Alpine from 'alpinejs'
import axios from 'axios'
import 'basecoat-css/all'

import './chat-entities.js'
import './toaster.js'

window.Alpine = Alpine
Alpine.plugin(ajax)
Alpine.start()

window.axios = axios
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo'
