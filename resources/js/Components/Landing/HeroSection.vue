<script setup lang="ts">
import { defineProps } from 'vue'; // Import defineProps
import Icon from '@/Components/UI/Icon.vue'; // Import Icon component
import FeatureHighlightCard from '@/Components/Shared/FeatureHighlightCard.vue'; // Import FeatureHighlightCard

interface FeatureHighlight {
    icon: string;
    text: string;
    backgroundColor?: string; // Optional, default can be set in the component
    textColor?: string; // Optional, default can be set in the component
    iconColor?: string; // Optional, default can be set in the component
}

interface HeroSectionData {
    title: string;
    subtitle: string;
    ctaButtonText: string;
    backgroundImageUrl: string;
    trustpilot: any; // Using any for simplicity now, can refine later if needed
    featureHighlights: FeatureHighlight[];
}

const props = defineProps<{
    heroData: HeroSectionData;
}>();

// No additional logic needed for now in this component
</script>

<template>
    <section id="hero-section"
        class="relative px-4 py-24 overflow-hidden text-center text-white hero-bg-image sm:py-32 bg-slate-700"
        :style="{ backgroundImage: `linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), url(${props.heroData.backgroundImageUrl})` }">
        <div class="container relative z-10 mx-auto">
            <h1 class="mb-6 text-4xl font-bold leading-tight sm:text-5xl md:text-6xl animate-fade-in-down font-poppins">
                {{ props.heroData.title }}
            </h1>
            <p class="max-w-3xl mx-auto mb-8 text-lg sm:text-xl text-slate-300 animate-fade-in-up font-inter">
                {{ props.heroData.subtitle }}
            </p>
            <!-- Trustpilot Link -->
            <div class="mb-10 animate-fade-in-up" style="animation-delay: 0.2s">
                <a :href="props.heroData.trustpilot.reviewUrl" target="_blank" rel="noopener noreferrer"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-500 rounded-lg hover:bg-gray-600">
                    <span class="mr-2">Trustpilot <span style="color: #05CD99;">★★★★☆</span> {{
                        props.heroData.trustpilot.ratingText }}</span>
                    <span class="text-xs">({{ props.heroData.trustpilot.reviewsText }})</span>
                </a>
            </div>

            <!-- CTA Button -->
            <button @click="$emit('ctaClick')"
                class="px-8 py-3 text-lg font-semibold text-white transition-all duration-300 ease-in-out transform bg-blue-500 rounded-lg shadow-xl hover:bg-blue-800 hover:scale-105 animate-fade-in-up"
                style="animation-delay: 0.4s">
                {{ props.heroData.ctaButtonText }}
            </button>
        </div>

        <!-- Feature Highlights Section -->
        <div
            class="container relative z-10 grid grid-cols-1 gap-6 px-4 mx-auto mt-16 md:mt-24 sm:grid-cols-2 lg:grid-cols-4">
            <div v-for="(feature, index) in props.heroData.featureHighlights" :key="index"
                :style="{ animationDelay: `${0.5 + index * 0.1}s` }">
                <FeatureHighlightCard :iconName="feature.icon" :text="feature.text"
                    :backgroundColor="feature.backgroundColor" :textColor="feature.textColor"
                    :iconColor="feature.iconColor" />
            </div>
        </div>
    </section>
</template>

<style scoped>
/* Estilos específicos para HeroSection si son necesarios */
/* You can add animations defined in App.tsx CSS here if needed */
.animate-fade-in-down {
    animation: fadeInDown 0.8s ease-out forwards;
    opacity: 0;
}

.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out forwards;
    opacity: 0;
}

.animate-zoom-in {
    animation: zoomIn 0.6s ease-out forwards;
    opacity: 0;
    /* Start hidden for animation */
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}


@keyframes zoomIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }
}

.hero-bg-image {
    background-size: cover;
    background-position: center;
}
</style>
