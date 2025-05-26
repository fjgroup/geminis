<script setup lang="ts">
import { defineProps } from 'vue'; // Import defineProps
import Icon from '@/Components/Icon.vue'; // Import Icon component
import FeatureHighlightCard from '@/Components/Landing/FeatureHighlightCard.vue'; // Import FeatureHighlightCard

interface FeatureHighlight {
  icon: string;
  text: string;
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
  <section
    id="hero-section"
    class="relative px-4 py-24 overflow-hidden text-center text-white hero-bg-image sm:py-32 bg-slate-700"
    :style="{backgroundImage: `linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), url(${props.heroData.backgroundImageUrl})`}"
  >
    <div class="container relative z-10 mx-auto">
      <h1 class="mb-6 text-4xl font-bold leading-tight sm:text-5xl md:text-6xl animate-fade-in-down font-poppins">
        {{ props.heroData.title }}
      </h1>
      <p class="max-w-3xl mx-auto mb-8 text-lg sm:text-xl text-slate-300 animate-fade-in-up font-inter">
        {{ props.heroData.subtitle }}
      </p>
      <!-- Trustpilot Link (Placeholder - needs prop and logic from App.tsx) -->
      <!-- <div className="mb-10 animate-fade-in-up" style={{animationDelay: '0.2s'}}> ... </div> -->

      <!-- CTA Button (Placeholder - needs prop and event handler from App.tsx) -->
      <!-- <button onClick={onCtaClick} className="px-8 py-3 text-lg font-semibold text-white transition-all duration-300 ease-in-out transform rounded-lg shadow-xl bg-brand-blue hover:bg-brand-blue-dark hover:scale-105 animate-fade-in-up" style={{animationDelay: '0.4s'}}>
        {{ props.heroData.ctaButtonText }}
      </button> -->
    </div>

    <!-- Feature Highlights Section -->
    <div class="container relative z-10 grid grid-cols-1 gap-6 px-4 mx-auto mt-16 md:mt-24 sm:grid-cols-2 lg:grid-cols-4">
      <div v-for="(feature, index) in props.heroData.featureHighlights" :key="index" :style="{ animationDelay: `${0.5 + index * 0.1}s` }">
        <FeatureHighlightCard :iconName="feature.icon" :text="feature.text" />
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
    opacity: 0; /* Start hidden for animation */
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
