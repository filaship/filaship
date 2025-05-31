import { defineConfig } from 'vitepress'

// https://vitepress.dev/reference/site-config
export default defineConfig({
  title: "Filaship",

  description: "A new way to use docker in laravel",
  
  themeConfig: {
    search: {
      provider: 'local'
    },

    logo: '/images/logo.svg',

    nav: [
      { text: 'Home', link: '/' },
      { text: 'Documentation', link: '/introduction/' },
      { text: 'Contribuitors', link: '/contributors/' },
      { text: 'GitHub', link: 'https://github.com/filaship/filaship' },	
    ],

    sidebar: [
      {
        text: 'Introduction',
        items: [
          { text: 'Introduction', link: '/introduction/' },
          { text: 'Installation', link: '/introduction/installation' },
          { text: 'Contribuition Guide', link: '/introduction/contribution-guide' },
        ]
      },
      {
        collapsed: true,
        text: 'Internals',
        items: [
          { text: 'Docker Compose Parser', link: '/internals/parsing-docker-compose' },
        ]
      }
    ],

    socialLinks: [
      { icon: 'github', link: 'https://github.com/filaship/filaship' }
    ]
  },

  head: [
    ['link', { rel: 'icon', href: '/images/favicon.ico' }] 
  ]
})

