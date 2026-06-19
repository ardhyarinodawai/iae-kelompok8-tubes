# ---------- Stage 1: Dependencies ----------
FROM node:20-alpine AS deps
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

# ---------- Stage 2: Build ----------
FROM node:20-alpine AS build
WORKDIR /app

COPY --from=deps /app/node_modules ./node_modules
COPY . .

# vite build -> Nitro menghasilkan output SSR self-contained di .output
RUN npm run build && ls -la /app

# ---------- Stage 3: Production runtime ----------
FROM node:20-alpine AS runner
WORKDIR /app

ENV NODE_ENV=production
ENV PORT=8003
ENV HOST=0.0.0.0

# Output Nitro sudah self-contained (deps di-bundle), tidak perlu node_modules
COPY --from=build /app/.output ./.output

RUN addgroup -S appgroup && adduser -S appuser -G appgroup
USER appuser

EXPOSE 8003

CMD ["node", ".output/server/index.mjs"]