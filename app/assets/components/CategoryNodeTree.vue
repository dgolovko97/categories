<template>
  <li class="node-tree">
    <span class="label">{{ category.name }}</span>
    <button v-on:click="deleteCategories(category.id)">Удалить</button>
    <button v-on:click="handleEditCategories">Редактировать</button>
    <form v-on:submit.prevent="editCategories(category.id)" style="display: inline-block">
      <input v-if="isShowControlEditElements" v-model="editedCategoryName" required >
      <button v-if="isShowControlEditElements" type="submit">Сохранить</button>
      <button v-if="isShowControlEditElements" v-on:click="handleEditCategories">Отменить</button>
    </form>
    <ul v-if="category.children && category.children.length > 0">
      <node v-for="child in category.children" :category="child" :key="child.id" v-on:update:categories="categoriesUpdated"></node>
    </ul>
  </li>
</template>

<script>
import axios from "axios";

export default {
  name: "node",
  props: ['category'],
  data: function () {
    return {
      isShowControlEditElements: false,
      editedCategoryName: this.category.name,
      showEditInput: false
    }
  },
  methods: {
    categoriesUpdated: function (data) {
      this.$emit('update:categories', data)
    },
    handleEditCategories: function () {
      this.isShowControlEditElements = !this.isShowControlEditElements
    },
    deleteCategories: function (categoryId) {
      axios.delete('/categories?id=' + categoryId).then(response => {
        if (response.data.status === 'ok') {
          this.$emit('update:categories', response.data)
        } else if (response.data.status === 'error') {
          alert(response.data.errorMessage)
        }
      })

    },
    editCategories: function (categoryId) {
      axios.put('/categories?id=' + categoryId + '&name=' + this.editedCategoryName).then(response => {
        this.$emit('update:categories', response.data)
        this.isShowControlEditElements = false
      })
    },
  }
};
</script>