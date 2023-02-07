<template>
    <div class="pagination justify-content-center my-5">
        <button v-for="(page, index) in pages" :key="page" class="item"
            v-bind:class="{ 'current': page === current, 'noCurrent': page !== current, 'd-none': page != current && page != current - 1 && page != current + 1 && page != last && page != 1 }"
            @click="changePage(index)">
            <font-awesome-icon v-if="page == current + 1" icon="fa-solid fa-chevron-right"> </font-awesome-icon>
            <font-awesome-icon v-if="page == current - 1" icon="fa-solid fa-chevron-left"> </font-awesome-icon>
            <a v-if="page != current - 1 && page != current + 1"> {{ page }}</a>
        </button>
    </div>
</template>
  
<script>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faChevronLeft, faChevronRight } from '@fortawesome/free-solid-svg-icons'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faChevronLeft, faChevronRight)
// fazendo sumir com css... mas o ideal é: não entrar no v-for e/ou no array ter só os numeros corretos.

export default {
    name: 'PaginationOfLists',
    components: { FontAwesomeIcon },

    data() {
        return { last: 1 }
    },
    props: {
        offset: {
            type: [String, Number],
            default: 0,
        },
        total: {
            type: [String, Number],
            required: true,
        },
        limit: {
            type: [String, Number],
            default: 10,
        },
        current: {
            type: [String, Number],
            required: true,
        },
    },

    computed: {
        pages() {
            const qty = Math.ceil(this.total / this.limit);
            if (qty <= 1) {
                this.setLast(1);
                return [1];
            }
            let response = Array.from(Array(qty).keys(), (i) => i + 1);
            this.setLast(response[response.length - 1]);
            return response;
        },
    },
    methods: {
        changePage(offset) {
            this.$emit('change-page', offset);
        },
        setLast(number) {
            this.last = number;
        }
    },
};
</script>