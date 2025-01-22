<template>
  <Card class="mb-5 service-filter">
    <template #title>Search client</template>
    <template #content>
      <div class="flex flex-nowrap">
        <InputText
          v-model="clientName"
          @keyup.enter="onFilter"
          placeholder="Client name"
          class="flex-initial w-64"
        />
        <InputText
          v-model="clientIdCard"
          @keyup.enter="onFilter"
          class="mx-5"
          placeholder="Client id card"
        />

        <Button
          label="Search"
          severity="info"
          icon="pi pi-search"
          class="ml-auto"
          @click="onFilter"
        ></Button>
        <Button
          label="Clear search"
          severity="danger"
          icon="pi pi-del"
          class="ml-5"
          @click="clearFilter"
        ></Button>
      </div>
      <div class="flex mt-5" v-if="errorMessage">
        <Message
          severity="error"
          size="small"
          icon="pi pi pi-times-circle"
          class="w-full"
          >{{ errorMessage }}</Message
        >
      </div>
    </template>
  </Card>
</template>
<script setup>
import { Button, Card, InputText, Message } from "primevue";
import { ref } from "vue";

const emit = defineEmits(["onFilter", "clearFilter"]);

const clientName = ref("");
const clientIdCard = ref("");
const errorMessage = ref(null);

const onFilter = () => {
  errorMessage.value = null;

  if (clientName.value && clientIdCard.value) {
    errorMessage.value =
      "Search only Client name or Client ID card number but not both!";
  }

  if (!clientName.value && !clientIdCard.value) {
    errorMessage.value =
      "Please provide either a Client name or Client ID card number!";
  }

  if (clientIdCard.value && isNaN(clientIdCard.value)) {
    errorMessage.value = "Client ID card number must be a numeric value!";
  }

  if (!errorMessage.value) {
    emit("onFilter", {
      clientName: clientName.value,
      clientIdCard: clientIdCard.value,
    });
  }
};

const clearFilter = () => {
  clientName.value = "";
  clientIdCard.value = "";
  errorMessage.value = null;
  emit("clearFilter");
};
</script>
