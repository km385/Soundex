<template>
    <div :class="{
        'folded_player': isSidebarCollapsed,
        'unfolded_player': !isSidebarCollapsed
    }"
        class="border-l-4 border-t-2  border-[#060a13] media-player transition-w duration-500 h-36 fixed bottom-0 right-0 bg-gradient-to-t from-slate-900 via-slate-900 to-purple-900 ml-4">

        <!-- hidden audio player-->
        <audio ref="audioPlayer" controls="false" class="hidden">
            <source src="" type="audio/mpeg">
        </audio>

        <div class="relative">
            <img :src="computedCoverPath" class="h-20 w-20 object-cover m-2 border-2 rounded-md border-black">
            <div class="absolute ps-24 p-2 inset-0 flex flex-col justify-en  text-white">
                <h3 class="font-bold text-lg">{{ selectedSong.title }}</h3>
                <span class="opacity-70 text-base">{{ selectedSong.artist }}</span>
                <!--<span class="opacity-70 text-xs">{{ selectedSong.album }}</span>-->
            </div>
        </div>

        <div>
            <div class="relative h-1 bg-gray-400">
                <!--bar showing by width how much we listed to from 0 % to-->
                <div :style="{ width: progressPercentage + '%' }"
                    class="absolute h-full w-10 bg-[#0AFFED] flex items-center justify-end">
                    <div class="rounded-full w-3 h-3 bg-white shadow"></div>
                </div>
            </div>
        </div>

        <div class="flex justify-between text-base font-semibold text-gray-400 px-2 py-1">
            <div>
                <!--where are we in a song button-->
                {{ formattedCurrentTime }}
            </div>
            <!--previous button-->
            <div class="flex space-x-2 p-0.5">
                <button class="focus:outline-none">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="19 20 9 12 19 4 19 20"></polygon>
                        <line x1="5" y1="19" x2="5" y2="5"></line>
                    </svg>
                </button>
                <!--start and pouse button-->
                <button @click="togglePlayPause"
                    class="rounded-full w-8 h-8 flex items-center justify-center pl-1 ring-2 ring-gray-400 focus:outline-none">
                    <template v-if="!isPlaying">
                        <!-- Display the pause symbol when audio is playing -->
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="5 3 19 12 5 21 5 3"></polygon>
                        </svg>
                    </template>
                    <template v-else>
                        <!-- Display the play symbol when audio is paused -->
                        <svg class="w-6 h-6 pr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect x="5" y="3" width="4" height="18" fill="currentColor"></rect>
                            <rect x="15" y="3" width="4" height="18" fill="currentColor"></rect>
                        </svg>

                    </template>
                </button>

                <!--next button-->
                <button class="focus:outline-none">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="5 4 15 12 5 20 5 4"></polygon>
                        <line x1="19" y1="5" x2="19" y2="19"></line>
                    </svg>
                </button>

            </div>
            <div>
                <!--duration of the song-->
                {{ formattedDuration == null ? "00:00" : formattedDuration }}
            </div>
        </div>

    </div>
</template>

<script>
import { ref, computed, watch, onMounted } from 'vue';

export default {
    props: {
        selectedSong: Object,
        isSidebarCollapsed: Boolean,
    },

    setup(props) {
        const computedCoverPath = computed(() => {
            return props.selectedSong && props.selectedSong.cover_path
                ? props.selectedSong.cover_path
                : 'https://images.unsplash.com/photo-1500099817043-86d46000d58f?ixlib=rb-1.2.1&ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&auto=format&fit=crop&w=800&h=250&q=80';
        });

        const audioPlayer = ref(null);
        const currentTime = ref(0);
        const isPlaying = ref(false);

        const duration = computed(() =>
            props.selectedSong && props.selectedSong.duration_sec !== ''
                ? props.selectedSong.duration_sec
                : 0
        );
        const formattedDuration = computed(() => {
            const totalSeconds = Math.floor(duration.value);
            const minutes = Math.floor(totalSeconds / 60);
            const seconds = totalSeconds % 60;
            return `${minutes}:${seconds.toString().padStart(2, '0')}`;
        });

        function togglePlayPause() {
            if (audioPlayer.value.paused || audioPlayer.value.ended) {
                audioPlayer.value.play();
                isPlaying.value = true;
            } else {
                audioPlayer.value.pause();
                isPlaying.value = false;
            }
        }


        watch(() => props.selectedSong, async (newSelectedSong, _) => {
            console.log(`/database/songs/${newSelectedSong.id}`);
            if (newSelectedSong !== 'placeholder') {
                try {
                    const response = await axios.post(`/database/songs/${newSelectedSong.id}`);
                    isPlaying.value = false;
                    const songPath = response.data.songURL;
                    audioPlayer.value.src = songPath;


                    audioPlayer.value.play();
                    isPlaying.value = true;
                } catch (error) {
                    console.error('Error fetching song:', error);
                }
            }
        });

        onMounted(() => {
            audioPlayer.value.addEventListener('timeupdate', () => {
                currentTime.value = audioPlayer.value.currentTime;
            });

            audioPlayer.value.addEventListener('ended', () => {
                isPlaying.value = false;
            });
        });

        const formattedCurrentTime = computed(() => {
            const totalSeconds = Math.floor(currentTime.value);
            const minutes = Math.floor(totalSeconds / 60);
            const seconds = totalSeconds % 60;
            return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        });

        const progressPercentage = computed(() => {
            return Math.min((currentTime.value / duration.value) * 100 + 1, 100);
        });


        return {
            computedCoverPath,
            audioPlayer,
            currentTime,
            isPlaying,
            duration,
            togglePlayPause,
            formattedCurrentTime,
            formattedDuration,
            progressPercentage
        };
    },
};
</script>

<style scoped>
.folded_player {
    width: calc(100% - 5rem);
}

.unfolded_player {
    width: calc(100% - 12rem);
}
</style>
