import React from 'react';
import { SelectInput } from '../components/Select'

export default function AddMarkController({ endpoints }) {
    const [classList, sesClassList] = React.useState([])
    const [studentList, setStudentList] = React.useState([])
    const [subjectList, setSubjectList] = React.useState([])
    const [success, setSuccess] = React.useState('')
    const [error, setError] = React.useState('')

    const [markData, setMarkData] = React.useState({
        classGroupId: null,
        studentId: null,
        subjectId: null,
        markValue: null,
        description: null,
    });

    React.useEffect(() => {
        fetch(endpoints.classList)
            .then(res => res.json())
            .then((data) => {
                sesClassList(data.data)
            })

        fetch(endpoints.subjectList)
            .then(res => res.json())
            .then(data => {
                setSubjectList(data.data)
            })
    }, [])

    React.useEffect(() => {
        if (!markData.classGroupId) return

        fetch(endpoints.studentsList.replace(encodeURI('%classGroupId%'), markData.classGroupId))
            .then(res => res.json())
            .then(data => {
                setStudentList(data.data)
            })
    }, [markData.classGroupId])

    const handleSubmit = async (e) => {
        e.preventDefault()
        fetch(endpoints.submit, {method: 'POST', body: JSON.stringify(markData)})
            .then(async (res) => {
                const json = await res.json()

                if (res.status !== 200) {
                    setError(json?.messages?.join(', ') || json?.message)
                    setSuccess('')
                    return 
                }

                setError('')
                setSuccess(json?.success || 'Poprawnie dodano ocenę')
            })
            .catch(err => {
                setError(err.message)
                setSuccess('')
            })
    }

    const onClassGroupSelect = (classGroupId) => {
        updateMarkData({
            classGroupId: classGroupId
        })

        setStudentList([])
    }

    const updateMarkData = (newData) => {
        setMarkData(old => ({
            ...old,
            ...newData
        }))
    }

    const isValid = React.useMemo(() => {
        return Object.entries(markData).filter(([key]) => key !== 'description').every(([_, value]) => Boolean(value))
    })

    return (
        <div className='w-50 m-auto'>
            <h2 className='my-3'>Dodaj ocenę</h2>
            {success ? <div className="alert alert-success" role="alert">{success}</div> : null}
            {error ? <div className="alert alert-danger" role="alert">{error}</div> : null}
            <form onSubmit={handleSubmit}>
                <SelectInput
                    required
                    label={'Wybierz klasę z listy'} 
                    onChange={onClassGroupSelect} 
                    options={classList}
                />

                {studentList.length ? 
                    <SelectInput
                        required
                        label={'Wybierz ucznia'} 
                        onChange={(id) => updateMarkData({ studentId: id })} 
                        options={studentList} 
                    /> 
                    : null
                }

                {markData.studentId !== null ? 
                    <SelectInput
                        required
                        label={'Wybierz przedmiot'} 
                        onChange={(id) => updateMarkData({ subjectId: id })} 
                        options={subjectList} /> 
                    : null
                
                }

                {markData.studentId ? (
                    <div className='row'>
                        <div className="col form-group">
                            <label htmlFor="markValue">Ocena <span className="text-danger">*</span></label>
                            <input
                                required
                                type="number" 
                                className="form-control"
                                onChange={(e) => updateMarkData({ markValue: Number(e.target.value) })} 
                                id="markValue"
                                step={0.5}
                                min="1"
                                max="61"
                                placeholder="Podaj ocene">
                            </input>
                        </div>
                        <div className="col form-group">
                            <label htmlFor="markValue">Opis Oceny</label>
                            <input
                                type="text"
                                className="form-control"
                                onChange={({ target: { value } }) => updateMarkData({ description: value ? value : null })} 
                                id="markValue"
                                maxLength="255"
                                minLength="10"
                                placeholder="Podaj opis">
                            </input>
                        </div>
                    </div>) : null
                }
                <button disabled={!isValid} className='btn btn-success'>Zapisz</button>
            </form>
        </div>
    );
}