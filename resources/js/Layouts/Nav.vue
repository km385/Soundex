<template>
    <nav class="">
        <ul class="flex space-x-4 list-inside">
            <li>
                <NavLink href="/" :active="$page.component === 'Home'" >Home</NavLink>
            </li>

            <li>
                <NavLink href="/users" :active="$page.component === 'Users'" >Users</NavLink>
            </li>

            <li>
                <NavLink href="/metadata" :active="$page.component === 'Metadata'" >Metadata</NavLink>
            </li>

            <li>
                <NavLink href="/upload" :active="$page.component === 'Upload'" >Upload</NavLink>
            </li>

            <li>
                <NavLink href="/cutter" :active="$page.component === 'Cutter'" >Cutter</NavLink>
            </li>

            <li>
                <NavLink href="/files" :active="$page.component === 'Files'" >Files</NavLink>
            </li>

            <li>
                <NavLink href="/merge" :active="$page.component === 'Merge'" >Merge</NavLink>
            </li>

            <li>
                <NavLink href="/recorder" :active="$page.component === 'Recorder'" >Recorder</NavLink>
            </li>

            <li>
                <NavLink href="/speedup" :active="$page.component === 'SpeedUp'" >SpeedUp</NavLink>
            </li>

            <li>
                <NavLink href="/layermixer" :active="$page.component === 'LayerMixer'" >Layer Mixer </NavLink>
            </li>

            <li v-if="$page.props.auth.user">
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out"
                >
                    Log Out
                </Link>
            </li>

            <li v-else>
                <Link
                    href="/login"
                    method="get"
                    as="button"
                    class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out"
                >
                    Login
                </Link>
                <Link
                    href="/register"
                    method="get"
                    as="button"
                    class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out"
                >
                    Register
                </Link>
            </li>

        </ul>
    </nav>
</template>

<script>
import NavLink from "@/Components/NavLink.vue";

export default {
    name: "Nav",
    components: {NavLink},

}
</script>

<style scoped>

</style>
