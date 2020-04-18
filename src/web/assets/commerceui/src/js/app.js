import Vue from 'vue'
import App from './OrderDetails'
import 'prismjs/themes/prism.css'
import OrderMeta from './OrderMeta'
import OrderActions from './OrderActions'
import OrderCustomer from './OrderCustomer'
import OrderErrors from './OrderErrors'
import OrderSecondaryActions from './OrderSecondaryActions'
import store from './store'
import {t} from './filters/craft'
import {capitalize} from './filters/capitalize';
import BtnLink from './components/BtnLink'
import OrderBlock from './components/OrderBlock'
import OrderTitle from './components/OrderTitle'


Vue.config.productionTip = false
if (process.env.NODE_ENV === 'development') {
    Vue.config.devtools = true
}
Vue.filter('t', t)
Vue.filter('capitalize', capitalize)
Vue.component('btn-link', BtnLink)
Vue.component('order-block', OrderBlock)
Vue.component('order-title', OrderTitle)


// Order actions
// =========================================================================

window.OrderActionsApp = new Vue({
    render: h => h(OrderActions),
    store,
}).$mount('#order-actions-app')


// Order errors
// =========================================================================
window.OrderErrorsApp = new Vue({
    render: h => h(OrderErrors),
    store,
}).$mount('#order-errors-app')

// Order customer
// =========================================================================
window.OrderCustomerApp = new Vue({
    render: h => h(OrderCustomer),
    store,
}).$mount('#order-customer-app')


// Order details
// =========================================================================

window.OrderDetailsApp = new Vue({
    render: h => h(App),
    store,

    methods: {
        externalRefresh() {
            const draft = this.$store.state.draft
            this.$store.dispatch('recalculateOrder', draft)
                .then(() => {
                    this.$store.dispatch('displayNotice', "Order recalculated.")
                })
                .catch((error) => {
                    this.$store.dispatch('displayError', error);
                })
        }
    },

    mounted() {
        this.$store.dispatch('getOrder')
        this.$store.dispatch('getPurchasables')
    }
}).$mount('#order-details-app')


// Order meta
// =========================================================================

window.OrderMetaApp = new Vue({
    render: h => h(OrderMeta),
    store,
}).$mount('#order-meta-app')


// Order secondary actions
// =========================================================================

window.OrderSecondaryActionsApp = new Vue({
    render: h => h(OrderSecondaryActions),
    store,
}).$mount('#order-secondary-actions-app')
