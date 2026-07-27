<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    team_name: '',
    npsn: '',
    jenjang: '',
    alamat: '',
    no_telp: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Name" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

             <!-- Nama Pangkalan / Sekolah -->
            <div class="mt-4">
                <label class="block font-semibold mb-1">
                    Nama Pangkalan / Sekolah
                </label>

                <input
                    v-model="form.team_name"
                    type="text"
                    class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                    placeholder="Contoh: MTs NU Jepara"
                />

                <div
                    v-if="form.errors.team_name"
                    class="text-red-600 text-sm mt-1"
                >
                    {{ form.errors.team_name }}
                </div>
            </div>

            <!-- NPSN -->
            <div class="mt-4">
                <label class="block font-semibold mb-1">NPSN</label>

                <input
                    v-model="form.npsn"
                    type="text"
                    class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                    placeholder="Opsional"
                />

                <div v-if="form.errors.npsn" class="text-red-600 text-sm mt-1">
                    {{ form.errors.npsn }}
                </div>
            </div>

            <!-- Jenjang -->
            <div class="mt-4">
                <label class="block font-semibold mb-1">Jenjang</label>

                <select
                    v-model="form.jenjang"
                    class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                >
                    <option value="">Pilih Jenjang</option>
                    <option value="SD">SD</option>
                    <option value="MI">MI</option>
                    <option value="SMP">SMP</option>
                    <option value="MTs">MTs</option>
                    <option value="SMA">SMA</option>
                    <option value="MA">MA</option>
                    <option value="SMK">SMK</option>
                </select>

                <div
                    v-if="form.errors.jenjang"
                    class="text-red-600 text-sm mt-1"
                >
                    {{ form.errors.jenjang }}
                </div>
            </div>

            <!-- Alamat Pangkalan -->
            <div class="mt-4">
                <label class="block font-semibold mb-1">Alamat Pangkalan</label>

                <textarea
                    v-model="form.alamat"
                    rows="3"
                    class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                    placeholder="Alamat sekolah / pangkalan"
                />

                <div
                    v-if="form.errors.alamat"
                    class="text-red-600 text-sm mt-1"
                >
                    {{ form.errors.alamat }}
                </div>
            </div>

            <!-- No. Telepon -->
            <div class="mt-4">
                <label class="block font-semibold mb-1">No. Telepon</label>

                <input
                    v-model="form.no_telp"
                    type="text"
                    class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                    placeholder="Contoh: 081234567890"
                />

                <div
                    v-if="form.errors.no_telp"
                    class="text-red-600 text-sm mt-1"
                >
                    {{ form.errors.no_telp }}
                </div>
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirm Password" />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-4">
                <InputLabel for="terms">
                    <div class="flex items-center">
                        <Checkbox id="terms" v-model:checked="form.terms" name="terms" required />

                        <div class="ms-2">
                            I agree to the <a target="_blank" :href="route('terms.show')" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Terms of Service</a> and <a target="_blank" :href="route('policy.show')" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Privacy Policy</a>
                        </div>
                    </div>
                    <InputError class="mt-2" :message="form.errors.terms" />
                </InputLabel>
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link :href="route('login')" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Already registered?
                </Link>

                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Register
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
