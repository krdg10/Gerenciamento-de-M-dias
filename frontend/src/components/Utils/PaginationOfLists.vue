<template>
    <div class="pagination">
        <button v-for="(page, index) in pages" :key="page" class="item"
            v-bind:class="{ 'current': page === current, 'd-none': page != current && page != current-1 && page != current + 1 && page != last && page != 1  }"
            @click="changePage(index)">
            <a v-if="page==current+1"> --|</a>
            <a v-if="page==current-1"> |--</a>
            <a v-if="page!=current-1 && page!= current+1"> {{ page }}</a>
        </button>
    </div>
</template>
  
<script>
// fazendo sumir com css... mas o ideal é: não entrar no v-for e/ou no array ter só os numeros corretos.
export default {
    name: 'PaginationOfLists',
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