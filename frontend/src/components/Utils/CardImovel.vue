<template>
    <div class="card">
        <div class="card-header">
            <slot name="card-header">
            </slot>
        </div>
        <div class="card-body">
            <slot name="card-body">
            </slot>
        </div>

        <div class="card-footer">
            <div class="d-flex justify-content-between">
                <slot name="card-footer">
                </slot>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    name: 'cardImovel',
}

</script>

