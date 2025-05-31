<script lang="ts" setup>
import { useStorage } from '@vueuse/core'
const contributors = useStorage<any[]>('contributors', [])
const fromRepo = (repo: string) =>
    fetch(`https://api.github.com/repos/filaship/${repo}/contributors`)
        .then((res) => res.json())
        .catch(() => [])
const getContributors = async () => {
    const users = await Promise.all([
        fromRepo('filaship'),
    ])
    contributors.value = users.reduce((acc, data = []) => {
        if (!Array.isArray(data)) {
            return acc
        }
        return [...acc, ...data.filter(i => i.login)]
    }, []).reduce((acc, user) => {
        const existingUser = acc.find(u => u.id === user.id)
        if (existingUser) {
            existingUser.contributions += user.contributions
            return acc
        }
        return [...acc, {
            id: user.id,
            username: user.login,
            contributions: user.contributions,
            avatar_url: user.avatar_url
        }]
    }, [])
}
getContributors()
</script>

<template>
    <div style="font-size: 1.125rem; text-align: center; line-height: 1.75rem; margin-top: 2.5rem; margin-bottom: 2.5rem; padding-left: 1.25rem; padding-right: 1.25rem;">
        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
            <a
                v-for="contributor of contributors"
                :key="contributor.id"
                v-tooltip="contributor.username"
                :href="`https://github.com/${contributor.username}`"
                :aria-label="contributor.username"
                rel="noopener noreferrer"
                target="_blank"
            >
                <img
                    :src="contributor.avatar_url"
                    :alt="contributor.username"
                    :aria-label="contributor.username"
                    loading="lazy"
                    width="50"
                    height="50"
                    style="width: 3.75rem; height: 3.75rem; min-width: 3.75rem; min-height: 3.75rem; border-radius: 9999px;"
                />
            </a>
        </div>
    </div>
</template>
