import { reactive, computed } from 'vue'

// Global reactive state
const state = reactive({
    loggedInUser: null
})

export function useAuth() {
    const login = (user) => {
        state.loggedInUser = user
    }

    const logout = () => {
        state.loggedInUser = null
    }

    const isLoggedIn = computed(() => !!state.loggedInUser)

    return {
        loggedInUser: computed(() => state.loggedInUser),
        isLoggedIn,
        login,
        logout
    }
}
