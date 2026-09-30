import { useState, useEffect, useRef } from 'react';
import { getNotifications, markNotificationRead } from '../services/notificationsService';

function NotificationsBell() {
  const [notifications, setNotifications] = useState([]);
  const [open, setOpen] = useState(false);
  const boxRef = useRef(null);

  const loadNotifications = () => {
    getNotifications().then((response) => setNotifications(response.data.notifications));
  };

  useEffect(() => {
    loadNotifications();
    const interval = setInterval(loadNotifications, 30000);
    return () => clearInterval(interval);
  }, []);

  useEffect(() => {
    const handleClickOutside = (e) => {
      if (boxRef.current && !boxRef.current.contains(e.target)) {
        setOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const unreadCount = notifications.filter((n) => !Number(n.is_read)).length;

  const handleOpen = () => {
    setOpen((prev) => !prev);
  };

  const handleItemClick = async (notification) => {
    if (!Number(notification.is_read)) {
      try {
        await markNotificationRead(notification.id);
        loadNotifications();
      } catch (err) {
        // ignore, list will just stay unread
      }
    }
  };

  return (
    <div ref={boxRef} style={{ position: 'relative' }}>
      <button
        onClick={handleOpen}
        style={{
          position: 'relative',
          background: 'none',
          border: 'none',
          color: '#e0e0e0',
          cursor: 'pointer',
          fontSize: '1.1rem',
          padding: '0.3rem',
        }}
      >
        Bell
        {unreadCount > 0 && (
          <span
            style={{
              position: 'absolute',
              top: '-2px',
              right: '-2px',
              backgroundColor: '#ff6b6b',
              color: '#fff',
              borderRadius: '999px',
              fontSize: '0.65rem',
              padding: '0.05rem 0.35rem',
              fontWeight: 'bold',
            }}
          >
            {unreadCount}
          </span>
        )}
      </button>

      {open && (
        <div
          style={{
            position: 'absolute',
            right: 0,
            top: '2.2rem',
            width: '300px',
            maxHeight: '350px',
            overflowY: 'auto',
            backgroundColor: '#242424',
            border: '1px solid #333',
            borderRadius: '6px',
            boxShadow: '0 4px 12px rgba(0,0,0,0.4)',
            zIndex: 10,
          }}
        >
          <div style={{ padding: '0.7rem 1rem', borderBottom: '1px solid #333', fontSize: '0.85rem', color: '#999' }}>
            Notifications
          </div>

          {notifications.length === 0 && (
            <div style={{ padding: '1rem', fontSize: '0.85rem', color: '#999' }}>No notifications yet.</div>
          )}

          {notifications.map((n) => {
            const isUnread = !Number(n.is_read);
            return (
              <div
                key={n.id}
                onClick={() => handleItemClick(n)}
                style={{
                  padding: '0.7rem 1rem',
                  borderBottom: '1px solid #2a2a2a',
                  fontSize: '0.85rem',
                  color: isUnread ? '#e0e0e0' : '#777',
                  backgroundColor: isUnread ? '#2a2a2a' : 'transparent',
                  cursor: 'pointer',
                }}
              >
                {n.message}
                <div style={{ fontSize: '0.7rem', color: '#666', marginTop: '0.2rem' }}>
                  {n.created_at}
                </div>
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}

export default NotificationsBell;
