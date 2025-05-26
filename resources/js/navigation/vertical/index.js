export default [
  {
    title: 'Home',
    to: { name: 'root' },
    icon: { icon: 'tabler-smart-home' },
  },
  {
    title: 'Notifications',
    to: { name: 'notifications' },
    icon: { icon: 'tabler-bell' },
  },
  {
    title: 'Second page',
    to: { name: 'second-page' },
    icon: { icon: 'tabler-file' },
  },
  {
    title: 'Administration',
    icon: { icon: 'fas fa-cogs' },
    children: [
      {
        title: 'Menu Management',
        to: { name: 'admin-menus' },
        icon: { icon: 'fas fa-bars' },
      },
      {
        title: 'Content Management',
        to: { name: 'admin-contents' },
        icon: { icon: 'fas fa-file-alt' },
      },
      {
        title: 'User Management',
        icon: { icon: 'fas fa-users' },
        children: [
          {
            title: 'Roles',
            to: { name: 'admin-roles' },
            icon: { icon: 'fas fa-user-tag' },
          },
          {
            title: 'Permissions',
            to: { name: 'admin-permissions' },
            icon: { icon: 'fas fa-shield-alt' },
          },
          {
            title: 'User Roles',
            to: { name: 'admin-user-roles' },
            icon: { icon: 'fas fa-user-cog' },
          },
        ],
      },
    ],
  },
]
