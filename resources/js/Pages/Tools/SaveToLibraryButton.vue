<script setup>
  import {useForm} from "@inertiajs/vue3";

  const props = defineProps({
      fileLink: String
  })

  function onClick() {
      const formData = new FormData()
      formData.append('token', props.fileLink)
      axios.post('/savetolibrary', formData)
          .then(res => {
              console.log(res)
          })
  }
</script>

<template>
  <button type="button" @click="onClick" class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">
    Save to Library
  </button>
</template>

<style scoped>

</style>
