<script setup>
defineOptions({
  name: 'AppCombobox',
  inheritAttrs: false,
})

const elementId = computed(() => {
  const attrs = useAttrs()
  const _elementIdToken = attrs.id
  const _id = useId()
  
  // If an explicit ID is provided (like from accessibility composable), use it directly
  // Otherwise, generate a prefixed ID for consistency
  return _elementIdToken || _id
})

const label = computed(() => useAttrs().label)
</script>

<template>
  <div
    class="app-combobox flex-grow-1"
    :class="$attrs.class"
  >
    <VLabel
      v-if="label"
      :for="elementId"
      class="mb-1 text-body-2"
      :text="label"
    />

    <VCombobox
      v-bind="{
        ...$attrs,
        class: null,
        label: undefined,
        variant: 'outlined',
        id: elementId,
        menuProps: {
          contentClass: [
            'app-inner-list',
            'app-combobox__content',
            'v-combobox__content',
            $attrs.multiple !== undefined ? 'v-list-select-multiple' : '',
          ],
        },
      }"
    >
      <template
        v-for="(_, name) in $slots"
        #[name]="slotProps"
      >
        <slot
          :name="name"
          v-bind="slotProps || {}"
        />
      </template>
    </VCombobox>
  </div>
</template>
