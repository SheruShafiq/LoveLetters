/* eslint-disable no-unused-vars */
import { useState, useEffect } from 'react'
import reactLogo from './assets/react.svg'
import viteLogo from '/vite.svg'
import './App.css'
import { TextField, Button, Snackbar, Alert } from '@mui/material'

function App() {
  const [count, setCount] = useState(0)
  const [username, setUsername] = useState('')
  const [password, setPassword] = useState('')
  const [message, setMessage] = useState('')
  const [accounts, setAccounts] = useState([])
  const [isLoggedIn, setIsLoggedIn] = useState(false)
  const [open, setOpen] = useState(false)
  const [showLogin, setShowLogin] = useState(true)

  useEffect(() => {
    const loggedInUser = localStorage.getItem('user')
    if (loggedInUser) {
      setIsLoggedIn(true)
    }
  }, [])

  const handleCreateAccount = (e) => {
    e.preventDefault()
    const apiUrl = 'http://localhost:8000/create-account'
    fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ username, password })
    })
      .then(response => response.json())
      .then(data => {
        setMessage(data.message || data.error)
        setOpen(true)
      })
      .catch(error => {
        setMessage('Error creating account')
        setOpen(true)
        console.error('Error creating account:', error)
      })
  }

  const handleLogin = (e) => {
    e.preventDefault()
    const apiUrl = 'http://localhost:8000/login'
    fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ username, password })
    })
      .then(response => response.json())
      .then(data => {
        if (data.message) {
          setMessage(data.message)
          localStorage.setItem('user', username)
          setIsLoggedIn(true)
        } else {
          setMessage(data.error)
        }
        setOpen(true)
      })
      .catch(error => {
        setMessage('Error logging in')
        setOpen(true)
        console.error('Error logging in:', error)
      })
  }

  const handleLogout = () => {
    localStorage.removeItem('user')
    setIsLoggedIn(false)
  }

  const handleViewAccounts = () => {
    const apiUrl = 'http://localhost:8000/view-accounts'
    fetch(apiUrl)
      .then(response => response.json())
      .then(data => setAccounts(data))
      .catch(error => {
        setMessage('Error fetching accounts')
        setOpen(true)
        console.error('Error fetching accounts:', error)
      })
  }

  const handleClose = () => {
    setOpen(false)
  }

  return ( 
    <>
      {!isLoggedIn ? (
        <>
          {showLogin ? (
            <form onSubmit={handleLogin}>
              <h2>Login</h2>
              <TextField 
                label="Username" 
                value={username} 
                onChange={(e) => setUsername(e.target.value)} 
                required 
                fullWidth
                margin="normal"
              />
              <TextField 
                label="Password" 
                type="password" 
                value={password} 
                onChange={(e) => setPassword(e.target.value)} 
                required 
                fullWidth
                margin="normal"
              />
              <Button type="submit" variant="contained">Login</Button>
              <Button onClick={() => setShowLogin(false)}>Create Account</Button>
            </form>
          ) : (
            <form onSubmit={handleCreateAccount}>
              <h2>Create Account</h2>
              <TextField 
                label="Username" 
                value={username} 
                onChange={(e) => setUsername(e.target.value)} 
                required 
                fullWidth
                margin="normal"
              />
              <TextField 
                label="Password" 
                type="password" 
                value={password} 
                onChange={(e) => setPassword(e.target.value)} 
                required 
                fullWidth
                margin="normal"
              />
              <Button type="submit" variant="contained">Create Account</Button>
              <Button onClick={() => setShowLogin(true)}>Login</Button>
            </form>
          )}
        </>
      ) : (
        <>
          <Button variant="contained" onClick={handleLogout}>Logout</Button>
          <Button variant="contained" onClick={handleViewAccounts}>View Accounts</Button>
          {accounts.length > 0 && (
            <div className="accounts-list">
              <h2>Accounts:</h2>
              <ul>
                {accounts.map(account => (
                  <li key={account.id}>{account.username}</li>
                ))}
              </ul>
            </div>
          )}
        </>
      )}
      <Snackbar open={open} autoHideDuration={6000} onClose={handleClose}>
        <Alert onClose={handleClose} severity={message.includes('Error') ? 'error' : 'success'} sx={{ width: '100%' }}>
          {message}
        </Alert>
      </Snackbar>
    </>
  )
}

export default App
