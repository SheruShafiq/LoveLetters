/* eslint-disable no-unused-vars */
import { useState, useEffect } from 'react'
import reactLogo from './assets/react.svg'
import viteLogo from '/vite.svg'
import './App.css'

function App() {
  const [count, setCount] = useState(0)
  const [username, setUsername] = useState('')
  const [password, setPassword] = useState('')
  const [message, setMessage] = useState('')
  const [accounts, setAccounts] = useState([])



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
      .then(data => setMessage(data.message || data.error))
      .catch(error => console.error('Error creating account:', error))
  }

  const handleViewAccounts = () => {
    const apiUrl = 'http://localhost:8000/view-accounts'
    fetch(apiUrl)
      .then(response => response.json())
      .then(data => setAccounts(data))
      .catch(error => console.error('Error fetching accounts:', error))
  }

  return ( 
    <>
      <div>
        <a href="https://vitejs.dev" target="_blank">
          <img src={viteLogo} className="logo" alt="Vite logo" />
        </a>
        <a href="https://react.dev" target="_blank">
          <img src={reactLogo} className="logo react" alt="React logo" />
        </a>
      </div>
      <h1>Vite + React</h1>
      <div className="card">
        <button onClick={() => setCount((count) => count + 1)}>
          count is {count}
        </button>
        <p>
          Edit <code>src/App.jsx</code> and save to test HMR
        </p>
      </div>
      <p className="read-the-docs">
        Click on the Vite and React logos to learn more
      </p>
      
      <form onSubmit={handleCreateAccount}>
        <h2>Create Account</h2>
        <input 
          type="text" 
          placeholder="Username" 
          value={username} 
          onChange={(e) => setUsername(e.target.value)} 
          required 
        />
        <input 
          type="password" 
          placeholder="Password" 
          value={password} 
          onChange={(e) => setPassword(e.target.value)} 
          required 
        />
        <button type="submit">Create Account</button>
      </form>
      {message && <p>{message}</p>}
      <button onClick={handleViewAccounts}>View Accounts</button>
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
  )
}

export default App
