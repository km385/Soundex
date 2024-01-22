<template>
    <!-- Music player -->
    <div class=" h-36 flex flex-row background-photo mt-2 p-2">
        <!-- hidden audio player-->
        <audio ref="audioPlayer" controls="false" class="hidden">
            <source src="" type="audio/mpeg">
        </audio>
        <!-- Img  -->
        <div class="flex h-full p-2 pb-4">
            <img :src="computedCoverPath" class="w-auto h-full object-cover m-2 border-2 rounded-md border-black">
        </div>
        <div class=" flex flex-grow flex-col h-full mr-2">
            <!-- top with text and controls -->
            <div class="flex-grow flex justify-center flex-row w-full h-full ">
                <!-- song autor -->
                <div class="flex h-full p-2 pb-4 flex-1">
                    <div class=" p-2 inset-0 flex flex-col  text-white">
                        <h3 class="font-bold text-lg">{{ selectedSong.title }}</h3>
                        <span class="opacity-70 text-base">{{ selectedSong.artist }}</span>
                    </div>
                </div>
                <!-- controls -->
                <div class="flex-grow justify-center items-center flex mr-16 mt-6">
                    <!-- buttons -->
                    <div class="flex space-x-2 p-0.5 ">
                        <button class="focus:outline-none">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="19 20 9 12 19 4 19 20"></polygon>
                                <line x1="5" y1="19" x2="5" y2="5"></line>
                            </svg>
                        </button>
                        <!--start and pouse button-->
                        <button @click="togglePlayPause"
                            class="rounded-full w-8 h-8 flex items-center justify-center pl-1 ring-2 ring-white focus:outline-none">
                            <template v-if="!isPlaying">
                                <!-- Display the pause symbol when audio is playing -->
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                </svg>
                            </template>
                            <template v-else>
                                <!-- Display the play symbol when audio is paused -->
                                <svg class="w-6 h-6 pr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                </div>
                <!-- filler to secure center -->
                <div class="flex h-full p-2 pb-4 flex-1">
                </div>
            </div>
            <!-- bottom with bar and time -->
            <div class="flex flex-col h-screen">

                <!-- Bar showing by width 0,75rem - 42rem -->
                <div class="h-1/6 ">
                    <div class="relative h-1 bg-gray-400">
                        <div :style="{ width: progressWidth }"
                            class="h-full bg-[#0AFFED] flex items-center justify-end rounded-full">
                            <div class="rounded-full w-3 h-3 bg-white shadow"></div>
                        </div>
                    </div>
                </div>
                <!-- song time and duration -->
                <div class="flex-grow justify-between flex">
                    <div>{{ formattedCurrentTime }}</div>
                    <div>{{ formattedDuration }} </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, watch, onMounted } from 'vue';

export default {
    props: {
        selectedSong: Object,
    },

    setup(props) {
        const computedCoverPath = computed(() => {
            return props.selectedSong && props.selectedSong.cover_path && false
                ? props.selectedSong.cover_path
                : 'https://images-wixmp-ed30a86b8c4ca887773594c2.wixmp.com/f/eb777e7a-7d3c-487e-865a-fc83920564a1/d7kpm65-437b2b46-06cd-4a86-9041-cc8c3737c6f0.jpg?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJ1cm46YXBwOjdlMGQxODg5ODIyNjQzNzNhNWYwZDQxNWVhMGQyNmUwIiwiaXNzIjoidXJuOmFwcDo3ZTBkMTg4OTgyMjY0MzczYTVmMGQ0MTVlYTBkMjZlMCIsIm9iaiI6W1t7InBhdGgiOiJcL2ZcL2ViNzc3ZTdhLTdkM2MtNDg3ZS04NjVhLWZjODM5MjA1NjRhMVwvZDdrcG02NS00MzdiMmI0Ni0wNmNkLTRhODYtOTA0MS1jYzhjMzczN2M2ZjAuanBnIn1dXSwiYXVkIjpbInVybjpzZXJ2aWNlOmZpbGUuZG93bmxvYWQiXX0.-bP80wHw6Jb8moQRsxURQxONZvAMnJ6xLDD8Es7mHps';
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
                try {
                    const response = await axios.post(`/database/songs/${newSelectedSong.id}`);
                    // console.log(response.data);
                    isPlaying.value = false;
                    const songPath = response.data.songURL;
                    audioPlayer.value.src = songPath;
                    audioPlayer.value.play();
                    isPlaying.value = true;
                } catch (error) {
                }
        },
  { immediate: true } 
);

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

        const progressWidth = computed(() => {
            const minRem = 0.75;
            const maxRem = 42;

            const percentage = Math.min((currentTime.value / duration.value) * 100, 100);
            const remValue = minRem + (maxRem - minRem) * (percentage / 100);
            const finalRem = isNaN(remValue) ? 0.75 : Math.min(Math.max(remValue, 0.75), 42);
            return `${finalRem}rem`;
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
            progressWidth
        };
    },
};
</script>

<style scoped>
</style>
