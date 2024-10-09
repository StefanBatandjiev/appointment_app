import preset from '../../../../vendor/filament/filament/tailwind.config.preset';
import colors from 'tailwindcss/colors';

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './vendor/guava/calendar/resources/**/*.blade.php',
    ],
    theme: {
        extend: {
            textColor: colors,
        },
    },
    plugins: [],
};
