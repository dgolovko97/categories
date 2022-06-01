<template>
  <div class="tree">
    <ul class="tree-list">
      <category-node-tree v-on:update:categories="categoriesUpdated" :category="category" :key="category.id"></category-node-tree>
    </ul>
  </div>
</template>

<script>
import CategoryNodeTree from "./CategoryNodeTree";

export default {
  props: ['category'],
  components: {
    CategoryNodeTree
  },
  methods: {
    categoriesUpdated:function (data) {
      this.$emit('update:categories', data);
    }
  }
};
</script>

<style>
.tree-list ul {
  padding-left: 16px;
  margin: 6px 0;
}
</style>