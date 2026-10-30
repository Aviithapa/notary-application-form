<!-- ========== Start Stylesheet ========== -->
<link href="{{ asset('frontend/css/style.css') }}" rel="stylesheet" />
<link href="{{ asset('frontend/css/media.css') }}" rel="stylesheet" />
<link href="{{ asset('frontend/css/about.css') }}" rel="stylesheet" />
<link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
      integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    />

<style>
  .pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
}

.pagination-info,
.pagination-dropdown,
.pagination-links {
    flex: 1;
}

.pagination {
  display: flex;
  justify-content: center;
}

.pagination a {
  padding: 8px 16px;
  margin: 0 5px;
  border: 1px solid var(--blue-color);
  color: var(--blue-color);
  text-decoration: none;
  border-radius: 4px;
  cursor: pointer;
}
.pagination .active {
  background-color: var(--blue-color);
  color: var(--white-color);
  padding: 7px 16px;
  margin: 0 5px; 
  margin-top: -6px;
  border-radius: 4px;
}

.npc_pagination a:hover {
  background-color: var(--blue-color);
  color: var(--white-color);
}

.npc__lang-switch {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: inherit;
  text-decoration: none;
}

.npc__lang-switch-label {
  font-size: 0.8rem;
  opacity: 0.6;
}

.npc__lang-switch:not(.is-nepali) .npc__lang-switch-label:first-child,
.npc__lang-switch.is-nepali .npc__lang-switch-label:last-child {
  opacity: 1;
  font-weight: 700;
}

.npc__lang-switch-track {
  position: relative;
  width: 34px;
  height: 18px;
  border-radius: 999px;
  background-color: var(--primary-color);
  transition: background-color 0.2s ease;
}

.npc__lang-switch-thumb {
  position: absolute;
  top: 2px;
  left: 2px;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background-color: var(--white-color);
  transition: transform 0.2s ease;
}

.npc__lang-switch.is-nepali .npc__lang-switch-track {
  background-color: var(--secondary-color);
}

.npc__lang-switch.is-nepali .npc__lang-switch-thumb {
  transform: translateX(16px);
}

.npc__auth-header-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.npc__back-site {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 999px;
  background-color: var(--primary-color);
  color: var(--white-color);
  font-size: 0.85rem;
  font-weight: 600;
  white-space: nowrap;
  text-decoration: none;
  transition: background-color 0.2s ease;
}

.npc__back-site:hover {
  background-color: var(--secondary-color);
}

@media screen and (min-width: 1024px) {
  .npc__footer-grid--3 {
    grid-template-columns: 2fr 1fr 1.5fr;
  }
}

@media screen and (max-width: 600px) {
  .npc__auth-header-actions {
    flex-direction: column-reverse;
    align-items: flex-end;
    gap: 0.5rem;
  }

  .npc__back-site {
    padding: 0.4rem 0.7rem;
  }

  .npc__back-site span {
    display: none;
  }

  .npc__header-middle-nav .npc__logo-wrapper {
    height: 4rem;
  }

  .npc__header-middle-nav .npc__header-title {
    font-size: 0.95rem;
    padding: 0 0.5rem;
  }
}
</style>