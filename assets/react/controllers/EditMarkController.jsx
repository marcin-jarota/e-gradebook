import React from 'react';
import { TextInput } from '../components/TextInput'

export default function EditMarkController({ endpoints, mark }) {
    const [success, setSuccess] = React.useState('')
    const [error, setError] = React.useState('')

    console.log(endpoints, mark)
    const [markData, setMarkData] = React.useState({
        markValue: null,
        description: null,
    });


    const handleSubmit = async (e) => {
        e.preventDefault()
        fetch(endpoints.submit, {method: 'POST', body: JSON.stringify(markData)})
            .then(async (res) => {
                const json = await res.json()

                if (res.status !== 200) {
                    setError(json?.errors?.join(', ') || 'Nie udało się edytować oceny')
                    setSuccess('');
                    return
                }

                setSuccess(json?.success || 'Ocena edytowana poprawnie');
                setError('');
            })
    }

    const handleDelete = async () => {
        try {
            const response = await fetch(endpoints.delete, {method: 'DELETE'})
            const data = await response.json()

            if (response.status !== 200) {
                setError(data?.errors?.join(', ') || data?.error)
                setSuccess('')
                return
            }

            window.location.href = endpoints.redirect
        } catch (err) {

        }
    }

    const updateMarkData = (newData) => {
        setMarkData(old => ({
            ...old,
            ...newData
        }))
    }

    return (
        <div className='w-50 m-auto'>
            <h2 className='my-3'>Edytuj ocenę</h2>
            {success ? <div className="alert alert-success" role="alert">{success}</div> : null}
            {error ? <div className="alert alert-danger" role="alert">{error}</div> : null}
            <form onSubmit={handleSubmit}>
                <TextInput disabled={true} type={'text'} defaultValue={mark?.student} label={'Uczeń'}/>
                <TextInput disabled={true} type={'text'} defaultValue={mark?.subjectName} label={'Przedmiot'}/>
                <div className='row'>
                    <div className="col">
                        <TextInput
                                id="mark"
                                required 
                                type='number' 
                                defaultValue={mark?.value} 
                                label={'Ocena'}
                                onChange={({ value }) => updateMarkData({ markValue: Number(value) })} 
                                step="0.5" 
                                min="1" 
                                max="6"
                        />
                    </div>
                    <div className="col">
                            <TextInput
                                id="description"
                                type='text'
                                onChange={({ value }) => updateMarkData({ description: value ? value : null })} 
                                defaultValue={mark?.description || ''} 
                                label={'Opis'} 
                                maxLength="255" 
                                minLength="10"
                        />
                    </div>
                </div>
                <div className="row">
                    <button disabled={false} className='btn btn-success'>Edytuj</button>
                    <button disabled={false} type="button" onClick={handleDelete} className='btn btn-danger ml-4'>Usuń</button>
                </div>
            </form>
        </div>
    );
}