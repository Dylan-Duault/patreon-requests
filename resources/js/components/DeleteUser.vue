<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ref, useTemplateRef } from 'vue';

import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';

const passwordInput = useTemplateRef('passwordInput');
const dialogVisible = ref(false);
</script>

<template>
    <div class="space-y-6">
        <HeadingSmall
            title="Delete account"
            description="Delete your account and all of its resources"
        />
        <div
            class="space-y-4 rounded-lg border border-[var(--el-color-danger-light-5)] bg-[var(--el-color-danger-light-9)] p-4"
        >
            <div class="relative space-y-0.5 text-[var(--el-color-danger)]">
                <p class="font-medium">Warning</p>
                <p class="text-sm">
                    Please proceed with caution, this cannot be undone.
                </p>
            </div>
            <el-button type="danger" @click="dialogVisible = true" data-test="delete-user-button">
                Delete account
            </el-button>

            <el-dialog
                v-model="dialogVisible"
                title="Are you sure you want to delete your account?"
                width="500"
            >
                <Form
                    v-bind="ProfileController.destroy.form()"
                    reset-on-success
                    @error="() => passwordInput?.$el?.focus()"
                    :options="{
                        preserveScroll: true,
                    }"
                    class="space-y-6"
                    v-slot="{ errors, processing, reset, clearErrors }"
                >
                    <p class="text-[var(--el-text-color-secondary)]">
                        Once your account is deleted, all of its
                        resources and data will also be permanently
                        deleted. Please enter your password to confirm
                        you would like to permanently delete your
                        account.
                    </p>

                    <el-form-item>
                        <el-input
                            type="password"
                            name="password"
                            ref="passwordInput"
                            placeholder="Password"
                            show-password
                        />
                        <InputError :message="errors.password" />
                    </el-form-item>

                    <div class="flex justify-end gap-2">
                        <el-button
                            @click="() => {
                                clearErrors();
                                reset();
                                dialogVisible = false;
                            }"
                        >
                            Cancel
                        </el-button>

                        <el-button
                            type="danger"
                            native-type="submit"
                            :loading="processing"
                            data-test="confirm-delete-user-button"
                        >
                            Delete account
                        </el-button>
                    </div>
                </Form>
            </el-dialog>
        </div>
    </div>
</template>
